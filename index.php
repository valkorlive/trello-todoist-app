<?php

include_once 'config.php';
include_once 'src/Integration.php';
include_once 'src/Trello.php';
include_once 'src/Todoist.php';

// Экземпляры классов для работы
$todoist = new Todoist($config);
$integration = new Integration($config);
$trello = new Trello($config);

// Проверка списка текущих задач и отметка выполнених
$tasks = $todoist->getTasks();
$incompleteTasks = $integration->getIncompleteTasks();
// Обход списка невыполненых задач для сравнения с текущими задачи в Todoist
foreach ($incompleteTasks AS $incompleteTask) {
    $taskStatus = $todoist->getTaskCompleteDate($tasks, $incompleteTask['todoist_id']);
    if ($taskStatus['status'] == 'complete') {
        // Если задача является элементом чек-листа, отмечаем его выполненым, если нет - отмечаем выполненой карточку
        if ($incompleteTask['trello_checklist'] == 1) {
            $trello->setChecklistCompleteItem($incompleteTask['trello_card_id'], $incompleteTask['trello_id']);
        } else {
            $trello->setCardComplete($incompleteTask['trello_id']);
        }

        $integration->setTaskComplete($incompleteTask['id'], $taskStatus['date']);
    }
}

// Делаем выборку всех досок для создания меток
$boards = $trello->getBoards();
foreach ($boards AS $board) {
    // Проверяем наличие доски в БД
    $project = $integration->getProject($board['id']);

    if (!isset($project['id'])) {
        $label = $todoist->addLabel(['name' => $board['name']]);

        $labelId = $label['id'];
        $projectId = $integration->createProject([
            'name' => $board['name'],
            'board_id' => $board['id'],
            'label_id' => $labelId,
        ]);
    } else {
        $projectId = $project['id'];
        $labelId = $project['todoist_id'];

        // В случае изменения имени доски, перименовываем метку
        if ($project['name'] != $board['name']) {
            $integration->updateProject($projectId, $board['name']);
            $todoist->updateLabel($labelId, ['name' => $board['name']]);
        }
    }

    // Получаем список карточек в текущей доске, которые имеет дату выполнения
    $cards = $trello->getCards($project['trello_id']);
    foreach ($cards AS $card) {
        $task = $integration->getTask($card['id']);

        if (!isset($task['id'])) {
            $task = $todoist->addTask([
                'content' => $card['name'],
                'label_ids' => [$labelId],
                'due_datetime' => $card['due'],
            ]);
            $taskId = $task['id'];

            $integration->createTask($projectId, $taskId, $card, null, $card);
        } else {
            $taksId = $task['todoist_id'];

            // В случае изменения карточки (имя или дата), редактируем текущую задачу
            if ($task['name'] != $card['name'] || $task['due_date'] != date('Y-m-d H:i:s', strtotime($card['due']))) {
                $integration->updateTask($task['id'], ['name' => $card['name'], 'due_date' => $card['due']]);
                $todoist->updateTask($taksId, ['content' => $card['name'], 'due_datetime' => $card['due']]);
            }
        }

        // Проверяем наличие чек-листа у карточки
        if (!empty($card['idChecklists'])) {
            // Обходим все чек-листы
            foreach ($card['idChecklists'] AS $checklistId) {
                $checkItems = $trello->getChecklist($checklistId);
                foreach ($checkItems['checkItems'] AS $checkItem) {
                    $checkItem = (array) $checkItem;
                    $task = $integration->getTask($checkItem['id']);

                    if (!isset($task['id']) && $checkItem['state'] == 'incomplete') {
                        $task = $todoist->addTask([
                            'content' => $checkItem['name'],
                            'label_ids' => [$labelId],
                            'due_datetime' => $card['due'],
                            'parent' => $taskId,
                        ]);

                        $integration->createTask($projectId, $task['id'], $card, $checklistId, $checkItem);
                    } else {
                        // В случае изменения пунтка чек-листа (имя) или карточки (дата), редактируем текущую задачу
                        if ($task['name'] != $checkItem['name'] || $task['due_date'] != date('Y-m-d H:i:s', strtotime($card['due']))) {
                            $integration->updateTask($task['id'], ['name' => $checkItem['name'], 'due_date' => $card['due']]);
                            $todoist->updateTask($task['todoist_id'], ['content' => $checkItem['name'], 'due_datetime' => $card['due']]);
                        }
                    }
                }
            }
        }
    }
}