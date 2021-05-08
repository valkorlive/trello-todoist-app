<?php


class Todoist
{
    /*
     * @var array $apiData
     */
    private $apiData = [
        'token' => '',
    ];

    /*
     * @param array $config
     * @return void
     */
    public function __construct($config)
    {
        $this->apiData = $config['todoist'];
    }

    /*
     * @param string $url
     * @return string $output
     */
    public function get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?token=' . $this->apiData['token']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);

        // обработка ошибок

        return $output;
    }

    /*
     * @param string $url
     * @param string $query
     * @return string $output
     */
    public function post($url, $query)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?token=' . $this->apiData['token']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $output = curl_exec($ch);
        curl_close($ch);

        // обработка ошибок

        return $output;
    }

    /*
     * Получение списка активных задач
     *
     * @return array $output
     */
    public function getTasks()
    {
        $output = $this->get('https://api.todoist.com/rest/v1/tasks');
        $output = json_decode($output);

        return $output;
    }

    /*
     * Проверка статуса задачи
     *
     * @param array $tasks
     * @param string $taskId
     * @return array
     */
    public function getTaskCompleteDate($tasks, $taskId)
    {
        foreach ($tasks AS $task) {
            if ($task->id == $taskId) {
                return ['status' => 'incomplete'];
            }
        }

        return ['status' => 'complete', 'date' => time()];
    }

    /*
     * Создание задачи
     *
     * @param array $query
     * @return array $output
     */
    public function addTask($query)
    {
        $output = $this->post('https://api.todoist.com/rest/v1/tasks', json_encode($query, JSON_NUMERIC_CHECK));
        $output = json_decode($output);

        return (array) $output;
    }

    /*
     * Создание метки
     *
     * @param array $query
     * @return array $output
     */
    public function addLabel($query)
    {
        $output = $this->post('https://api.todoist.com/rest/v1/labels', json_encode($query));
        $output = json_decode($output);

        return (array) $output;
    }

    /*
     * Обновление задачи
     *
     * @param string $id
     * @param array $query
     * @return void
     */
    public function updateTask($id, $query)
    {
        $this->post('https://api.todoist.com/rest/v1/tasks/' . $id, json_encode($query));
    }

    /*
     * Обновление метки
     *
     * @param string $id
     * @param array $query
     * @return void
     */
    public function updateLabel($id, $query)
    {
        $this->post('https://api.todoist.com/rest/v1/labels/' . $id, json_encode($query));
    }
}