<?php


class Integration
{
    /*
     * @var new PDO
     */
    private $db = null;

    /*
     * @param array $config
     * @return void
     */
    public function __construct($config) {
        // Создаем подулючение к БД
        $db = new PDO ('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'], $config['db']['user'], $config['db']['password']);
        $db->query('SET character_set_connection = ' . $config['db']['charset'] . ';');
        $db->query('SET character_set_client = ' . $config['db']['charset'] . ';');
        $db->query('SET character_set_results = ' . $config['db']['charset'] . ';');

        $this->db = $db;
    }

    /*
     * Создание проекта.
     *
     * @param array $data
     * @return int PDO::lastInsertId()
     */
    public function createProject($data)
    {
        $res = $this->db->prepare('INSERT IGNORE INTO projects (`created`, `name`, `trello_id`, `todoist_id`) VALUES (:created, :name, :trello_id, :todoist_id)');
        $res->execute([
            ':created' => date('Y-m-d H:i:s'),
            ':name' => $data['name'],
            ':trello_id' => $data['board_id'],
            ':todoist_id' => $data['label_id'],
        ]);

        return $this->db->lastInsertId();
    }

    /*
     * Создание задачи.
     *
     * @param int $projectId
     * @param string $taskId
     * @param array $card
     * @param null $checklistId
     * @param array $checkItem
     * @return void
     */
    public function createTask($projectId, $taskId, $card, $checklistId, $checkItem)
    {
        $checklist = 0;
        if ($checklistId != null) {
            $checklist = 1;
        }

        $res = $this->db->prepare("INSERT IGNORE INTO tasks (`created`, `due_date`, `name`, `project_id`, `trello_id`, `trello_card_id`, `trello_checklist`, `trello_checklist_id`, `todoist_id`)
                VALUES (:created, :due_date, :name, :project_id, :trello_id, :trello_card_id, :trello_checklist, :trello_checklist_id, :todoist_id)");
        $res->execute([
            ':created' => date('Y-m-d H:i:s'),
            ':due_date' => date('Y-m-d H:i:s', strtotime($card['due'])),
            ':name' => $checkItem['name'],
            ':project_id' => $projectId,
            ':trello_id' => $checkItem['id'],
            ':trello_card_id' => $card['id'],
            ':trello_checklist' => $checklist,
            ':trello_checklist_id' => $checklistId,
            ':todoist_id' => $taskId,
        ]);
    }

    /*
     * Обновление проекта.
     *
     * @param int $id
     * @param string $name
     * @return void
     */
    public function updateProject($id, $name)
    {
        $res = $this->db->prepare("UPDATE projects SET `name` = :name WHERE id = :id");
        $res->execute([
            ':name' => $name,
            ':id' => $id,
        ]);
    }

    /*
     * Обновление задачи.
     *
     * @param int $id
     * @param array $query
     * @return void
     */
    public function updateTask($id, $query)
    {
        $res = $this->db->prepare("UPDATE tasks SET `name` = :name, `due_date` = :due_date WHERE id = :id");
        $res->execute([
            ':name' => $query['name'],
            ':due_date' => date('Y-m-d H:i:s', strtotime($query['due_date'])),
            ':id' => $id,
        ]);
    }

    /*
     * Получение информации о проекте.
     *
     * @param string $id
     * @return array
     */
    public function getProject($id)
    {
        $res = $this->db->query("SELECT * FROM projects WHERE trello_id = '{$id}'");

        return $res->fetch();
    }

    /*
     * Получение информации о задаче.
     *
     * @param string $id
     * @return array
     */
    public function getTask($id)
    {
        $res = $this->db->query("SELECT * FROM tasks WHERE trello_id = '{$id}'");

        return $res->fetch();
    }

    /*
     * Получение списка невыполненных задач.
     *
     * @return array
     */
    public function getIncompleteTasks()
    {
        $res = $this->db->query("SELECT * FROM tasks WHERE todoist_status = 'incomplete'");

        return $res->fetchAll();
    }

    /*
     * Отметка задачи выполненной.
     *
     * @param int $id
     * @param string $date
     * @return void
     */
    public function setTaskComplete($id, $date)
    {
        $date = date('Y-m-d H:i:s', $date);

        $res = $this->db->prepare("UPDATE tasks SET trello_status = :trello_status, trello_updated = :trello_updated, todoist_status = :todoist_status, todoist_updated = :todoist_updated WHERE id = :id");
        $res->execute([
            ':trello_status' => 'complete',
            ':trello_updated' => $date,
            ':todoist_status' => 'complete',
            ':todoist_updated' => $date,
            ':id' => $id,
        ]);
    }
}