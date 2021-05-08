<?php


class Trello
{
    /*
     * @var array $apiData
     */
    private $apiData = [
        'key' => '',
        'token' => '',
    ];

    /*
     * @param array $config
     * @return void
     */
    public function __construct($config)
    {
        $this->apiData = $config['trello'];
    }

    /*
     * @param string $url
     * @return string $output
     */
    public function get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?key=' . $this->apiData['key'] . '&token=' . $this->apiData['token']);
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
    public function put($url, $query)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?key=' . $this->apiData['key'] . '&token=' . $this->apiData['token']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $output = curl_exec($ch);
        curl_close($ch);

        // обработка ошибок

        return $output;
    }

    /*
     * Получение списка досок
     *
     * @return array $boards
     */
    public function getBoards()
    {
        $output = $this->get('https://api.trello.com/1/members/me/boards');
        $output = json_decode($output);

        $boards = [];
        foreach ($output AS $board) {
            if ($board->closed) {
                continue;
            }

            $boards[] = [
                'name' => $board->name,
                'id' => $board->id,
            ];
        }

        return $boards;
    }

    /*
     * Получение списка карточек из доски
     *
     * @param string $id
     * @return array $cards
     */
    public function getCards($id)
    {
        $output = $this->get('https://api.trello.com/1/boards/' . $id . '/cards');
        $output = json_decode($output);

        $cards = [];
        foreach ($output AS $card) {
            if ($card->closed || $card->dueComplete || empty($card->due)) {
                continue;
            }

            $cards[] = (array) $card;
        }

        return $cards;
    }

    /*
     * Получение списка пунктов чек-листа
     *
     * @param string $id
     * @return array $output
     */
    public function getChecklist($id)
    {
        $output = $this->get('https://api.trello.com/1/checklists/' . $id);
        $output = json_decode($output);

        return (array) $output;
    }

    /*
     * Обновления пункта чек-листа, пометка как выполненного
     *
     * @param string $cardId
     * @param string $checkitemId
     * @return void
     */
    public function setChecklistCompleteItem($cardId, $checkitemId)
    {
        $this->put('https://api.trello.com/1/cards/' . $cardId . '/checkItem/' . $checkitemId, ['state' => 'complete']);
    }

    /*
     * Обновления карточки, пометка как выполненной
     *
     * @param string $cardId
     * @return void
     */
    public function setCardComplete($cardId)
    {
        $this->put('https://api.trello.com/1/cards/' . $cardId, ['dueComplete' => 1]);
    }
}