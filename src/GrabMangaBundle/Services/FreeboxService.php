<?php

namespace GrabMangaBundle\Services;

class FreeboxService {
    private $url;
    private $box;
    private $id;
    private $token = null;
    private $sessionToken = null;

    public function __construct() {
        $this->url = "http://mafreebox.free.fr";
        $this->id = "fr.freebox.torrent";
        $this->token = "b/ZbiA0wUpz1U40+/xe3HPFW7SB3wiSmsNXkFUmwkx/MqnhwIQby14uJUmrFlEHY";
        $this->sessionToken = null;
        $this->version();
    }

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function authorize($json) {
        try {
            $data = json_decode($json);
            $call = $this->call("login/authorize",
                array(
                    'app_id' => $data->app_id,
                    'app_name' => $data->app_name,
                    'app_version' => $data->app_version,
                    'device_name' => $data->device_name
                ));
            if (! $call->success) {
                throw new \Exception("Erreur d'autorisation : " . $call->msg);
            }
            return [
                "success" => true,
                "result" => [
                    "app_token" => $call->result->app_token,
                    "track_id" => $call->result->track_id,
                ],
            ];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'authorization : ". $ex->getMessage(), 500);
        }
    }

    public function trackAuthorize($trackId) {
        try {
            $callTracking = $this->call('login/authorize/' . $trackId);
            if ($callTracking instanceof \Exception) {
                throw new \Exception($callTracking->getMessage());
            }
            if (!$callTracking->success) {
                throw new \Exception("Erreur tracking ", 500);
            }
            return [
                "success" => true,
                "result" => [
                    "status" => $callTracking->result->status,
                ],
            ];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du tracking : ". $ex->getMessage(), 500);
        }
    }

    public function login() {
        try {
            $callLogin = $this->call("login");
            if (! $callLogin->success) {
                throw new \Exception("Erreur login : " . $callLogin->msg);
            }
            return [
                "success" => true,
                "result" => [
                    "challenge" => $callLogin->result->challenge,
                ],
            ];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la récupération de la session : ". $ex->getMessage(), 500);
        }
    }

    public function loginSession($json) {
        try {
            $data = json_decode($json);
            $callSession = $this->call("login/session",
                array(
                    'app_id' => $data->app_id,
                    'password' => $data->password
                ));
            if (! $callSession->success) {
                throw new \Exception("Erreur session : " . $callSession->msg);
            }
            return [
                "success" => true,
                "result" => [
                    "session_token" => $callSession->result->session_token,
                ],
            ];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la récupération de la session : ". $ex->getMessage(), 500);
        }
    }

    public function getDownloads($json) {
        try {
            $data = json_decode($json);
            $this->sessionToken = $data->token_session;
            $downloads = $this->call("downloads/", [], null, true);
            if (! $downloads['success']) {
                throw new \Exception("Erreur downloads : " . $downloads['msg']);
            }
            return $downloads;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la récupération des téléchargements : ". $ex->getMessage(), 500);
        }
    }

    public function getDownload($json) {
        try {
            $data = json_decode($json);
            $this->sessionToken = $data->token_session;
            $download = $this->call("downloads/".$data->id, [], null, true);
            if (! $download['success']) {
                throw new \Exception("Erreur downloads : " . $download['msg']);
            }
            return $download;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la récupération des téléchargements : ". $ex->getMessage(), 500);
        }
    }

    public function deleteDownload($json) {
        try {
            $data = json_decode($json);
            $this->sessionToken = $data->token_session;
            $downloadDelete = $this->call("downloads/".$data->id, [], 'DELETE', true);
            if (! $downloadDelete['success']) {
                throw new \Exception("Erreur downloads : " . $downloadDelete['msg']);
            }
            return $downloadDelete;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la récupération des téléchargements : ". $ex->getMessage(), 500);
        }
    }

    public function setStatusDownload($json) {
        try {
            $data = json_decode($json);
            $this->sessionToken = $data->token_session;
            $status = $this->call("downloads/".$data->id, $data->param, 'PUT', true);
            if (! $status['success']) {
                throw new \Exception($status['msg']);
            }
            return $status;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la mise à jour du status : ". $ex->getMessage(), 500);
        }
    }

    public function addDownloadByUrl($json) {
        try {
            $data = json_decode($json, true);
            $this->sessionToken = $data['token_session'];
            $parameters = [
                'download_url' => "http%3A%2F%2Fwww.torrents9.pe%2Fget_torrent%2Fvikings-s05e03-vostfr-bluray-720p-hdtv.torrent"
            ];
            $add = $this->call("downloads/add", $parameters, 'POST', true);
            if (! $add['success']) {
                throw new \Exception($add['msg']);
            }
            return $add;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'ajout du téléchargement par url : ". $ex->getMessage(), 500);
        }
    }


    private function version() {
        try {
            $path = "api_version";
            $content = file_get_contents("$this->url/$path");
            return $this->box = json_decode($content);
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la récupération de la version : ". $ex->getMessage(), 500);
        }
    }

    private function call($api_url, $params = array(), $method = null, $returnArray=false) {
        try {
            if (! $method) {
                $method = (! $params) ? 'GET' : 'POST';
            }
            $rurl = $this->url . $this->box->api_base_url . 'v' . intval($this->box->api_version) .
                '/' . $api_url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $rurl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            if ($method == "POST") {
                curl_setopt($ch, CURLOPT_POST, true);
            } elseif ($method == "DELETE") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            } elseif ($method == "PUT") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            }
            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
            if ($this->sessionToken) {
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array(
                        "X-Fbx-App-Auth: $this->sessionToken",
                        "Content-Type: application/x-www-form-urlencoded"
                    ));
            }
            $content = curl_exec($ch);
            curl_close($ch);
            $r = json_decode($content, $returnArray);
            return $r;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'appel <".$api_url.">: ". $ex->getMessage(), 500);
        }
    }
}