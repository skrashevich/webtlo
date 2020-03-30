<?php

/**
 * Class DownloadStation
 * Supported by Download Station on Synolog Inc.
 */
class DownloadStation extends TorrentClient
{

    protected static $base = 'https://%s:%s/webapi/%s';

    /**
     * @var object информации об API
     */
    protected $apiInfo;

    /**
     * @var array общие коды ошибок
     */
    protected $commonErrorCodes = array(
        100 => 'Unknown error',
        101 => 'Invalid parameter',
        102 => 'The requested API does not exist',
        103 => 'The requested method does not exist',
        104 => 'The requested version does not support the functionality',
        105 => 'The logged in session does not have permission',
        106 => 'Session timeout',
        107 => 'Session interrupted by duplicate login',
    );

    /**
     * @var array коды ошибок авторизации
     */
    protected $authErrorCodes = array(
        400 => 'No such account or incorrect password',
        401 => 'Account disabled',
        402 => 'Permission denied',
        403 => '2-step verification code required',
        404 => 'Failed to authenticate 2-step verification code',
    );

    /**
     * @var array коды ошибок при работе с раздачами
     */
    protected $taskErrorCodes = array(
        400 => 'File upload failed',
        401 => 'Max number of tasks reached',
        402 => 'Destination denied',
        403 => 'Destination does not exist',
        404 => 'Invalid task id',
        405 => 'Invalid task action',
        406 => 'No default destination',
        407 => 'Set destination failed',
        408 => 'File does not exist',
    );

    /**
     * получение информации об API и запись его в $this->apiInfo
     * @return bool true в случе успеха, false в случае неудачи
     */
    protected function getApiInfo()
    {
        $api = 'SYNO.API.Info';
        $path = 'query.cgi';
        $fields = array(
            'api' => $api,
            'method' => 'query',
            'query' => 'ALL',
            'version' => 1
        );
        $responseApiInfo = $this->makeRequest($path, $fields);
        if ($responseApiInfo === false) {
            return false;
        }
        $this->apiInfo = $responseApiInfo;
        return true;
    }

    /**
     * получение идентификатора сессии (SID) и запись его в $this->sid
     * @return bool true в случе успеха, false в случае неудачи
     */
    protected function getSID()
    {
        $responseApiInfo = $this->getApiInfo();
        if ($responseApiInfo === false) {
            return false;
        }
        $api = 'SYNO.API.Auth';
        $path = $this->apiInfo->{$api}->path;
        $fields = array(
            'api' => $api,
            'method' => 'login',
            'account' => $this->login,
            'passwd' => $this->password,
            'version' => $this->apiInfo->{$api}->maxVersion
        );
        $responseAuthApi = $this->makeRequest($path, $fields, 'auth');
        if ($responseAuthApi === false) {
            return false;
        }
        $this->sid = $responseAuthApi->sid;
        return true;
    }

    /**
     * выполнение запроса
     * @param string $path
     * @param string $fields
     * @param string $apiErrorCode
     * @return bool|mixed|string
     */
    private function makeRequest($path, $fields, $apiErrorCode = '')
    {
        $this->ch = curl_init();
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => sprintf(self::$base, $this->host, $this->port, $path),
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = curl_exec($this->ch);
        if ($response === false) {
            Log::append('CURL ошибка: ' . curl_error($this->ch));
            return false;
        }
        curl_close($this->ch);
        $response = json_decode($response);
        if ($response->success) {
            return $response->data;
        }
        // описание ошибки
        $responseErrorDescription = array();
        if (isset($this->commonErrorCodes[$response->error->code])) {
            $responseErrorDescription = $this->commonErrorCodes[$response->error->code];
        } elseif ($apiErrorCode == 'auth') {
            if (isset($this->authErrorCodes[$response->error->code])) {
                $responseErrorDescription = $this->authErrorCodes[$response->error->code];
            }
        } elseif ($apiErrorCode == 'task') {
            if (isset($this->taskErrorCodes[$response->error->code])) {
                $responseErrorDescription = $this->taskErrorCodes[$response->error->code];
            }
        }
        Log::append('Error: ' . $responseErrorDescription);
        return false;
    }

    public function getTorrents()
    {
        $api = 'SYNO.DownloadStation.Task';
        $path = $this->apiInfo->{$api}->path;
        $fields = array(
            'api' => $api,
            '_sid' => $this->sid,
            'method' => 'list',
            'version' => $this->apiInfo->{$api}->maxVersion,
            'additional' => 'detail,transfer'
        );
        $responseTaskApi = $this->makeRequest($path, $fields, 'task');
        if ($responseTaskApi === false) {
            return false;
        }
        $torrents = array();
        foreach ($responseTaskApi->tasks as $torrent) {
            if ($torrent->status != 'error') {
                $progress = $torrent->size - $torrent->additional->transfer->size_downloaded;
                if ($progress == 0) {
                    if ($torrent->status == 'paused') {
                        $status = -1;
                    } elseif ($torrent->status == 'seeding') {
                        $status = 1;
                    }
                } elseif ($torrent->status == 'downloading') {
                    $status = 0;
                } else {
                    continue;
                }
                // не возвращает хэш раздачи, вообще никак...
                $hash = strtoupper($torrent['hash']);
                $torrents[$hash] = $status;
            }
        }
        return $torrents;
    }

    public function addTorrent($torrentFilePath, $savePath = '')
    {
        // ss
    }

    public function setLabel($hashes, $label = '')
    {
        // ss
    }

    public function startTorrents($hashes, $force = false)
    {
        // ss
    }

    public function stopTorrents($hashes)
    {
        // ss
    }

    public function removeTorrents($hashes, $deleteLocalData = false)
    {
        // ss
    }
}
