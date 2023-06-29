<?php

if (!defined("ACCESS_BILLING")) die("DIRECT ACCESS DENIED.");

use GuzzleHttp\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class prostotv {

	private $url,
			$version,
			$login,
			$password,
			$token;

	public 	$apptitle,
			$apptime,
			$time_next_removal,
			$client,
			$logger;


	public function __construct() {
		global $config;

		$this->apptitle = "PROSTOTV";
		$this->apptime = time();

		// 1-ое число следующего месяца
		$this->time_next_removal = strtotime('midnight first day of next month');

		$this->url = $config["prostotv"]["host"];
		$this->version = $config["prostotv"]["version"];
		$this->login = $config["prostotv"]["login"];
		$this->password = $config["prostotv"]["password"];

		$this->client = new Client([
	        "base_uri" => $this->url,
	    ]);

	    $this->logger = new Logger("prostotv");
		$this->logger->pushHandler(new StreamHandler(__DIR__."/../../logs/syslog.log", Logger::DEBUG));
		$this->logger->pushHandler(new FirePHPHandler());

	}

	private function getToken() {
		
		try {
			$api["response"]["post"] = $this->client->request("POST", "/".$this->version."/tokens", [
											// "debug" => true,
											'json' => [
													'login' => $this->login,
													'password' => $this->password,
												]
										]);

			$api["response"]["body"] = $api["response"]["post"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			$this->token = $api["response"]["data"]["token"];
			
			return $this->token;

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function getUser($userId) {

		try {
			$api["response"]["get"] = $this->client->request("GET", "/".$this->version."/objects/".$userId, [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											'json' => [
													'account_id' => $userId,
												]
										]);

			$api["response"]["body"] = $api["response"]["get"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function createUser() {
		
		try {
			$api["response"]["post"] = $this->client->request("POST", "/".$this->version."/objects", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											'json' => [
													// 'first_name' => '',
													// 'middle_name' => '',
													// 'last_name' => '',
													// 'note' => '',
													// 'phone' => '',
													'password' => substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8),
												]
										]);

			$api["response"]["body"] = $api["response"]["post"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function deleteUser($userId) {

		try {
			$api["response"]["delete"] = $this->client->request("DELETE", "/".$this->version."/objects/".$userId, [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											'json' => [
													'account_id' => $userId,
												]
										]);

			$api["response"]["body"] = $api["response"]["delete"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	// $state = 'active' | 'disconnected'
	public function setUserState($userId, $state = 'active') {

		try {
			$api["response"]["post"] = $this->client->request("POST", "/".$this->version."/objects/".$userId."/status", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											'json' => [
													'status' => $state,
												]
										]);

			$api["response"]["body"] = $api["response"]["post"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function setUserPassword($userId, $password) {

		try {
			$api["response"]["put"] = $this->client->request("PUT", "/".$this->version."/objects/".$userId."/password", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											'json' => [
													'password' => $password,
												]
										]);

			$api["response"]["body"] = $api["response"]["put"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return true;

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	// auto_renewal = '0' | '1' - in fact enable & disable the service
	public function addService($userId, $serviceId) {

		try {
			$api["response"]["post"] = $this->client->request("POST", "/".$this->version."/objects/".$userId."/services", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											'json' => [
													'id' => $serviceId,
													'auto_renewal' => 1,
												]
										]);

			$api["response"]["body"] = $api["response"]["post"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function deleteServise($userId, $serviceId) {

		try {
			$api["response"]["delete"] = $this->client->request("DELETE", "/".$this->version."/objects/".$userId."/services/".$serviceId, [
											// "debug" => true,
											'headers' => [
											       'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["delete"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function getDevices($userId) {

		try {
			$api["response"]["get"] = $this->client->request("GET", "/".$this->version."/objects/".$userId."/devices", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["get"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"]["devices"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function addDevice($userId) {

		try {
			$api["response"]["post"] = $this->client->request("POST", "/".$this->version."/objects/".$userId."/devices", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["post"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function deleteDevice($userId, $deviceId) {

		try {
			$api["response"]["delete"] = $this->client->request("DELETE", "/".$this->version."/objects/".$userId."/devices/".$deviceId, [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["delete"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return true;

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function setDeviceOptions($userId, $deviceId, $options) {

		try {
			$api["response"]["put"] = $this->client->request("PUT", "/".$this->version."/objects/".$userId."/devices/".$deviceId, [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											'json' => [
													'comment' => $options['comment'],
												]
										]);

			$api["response"]["body"] = $api["response"]["put"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function getPlaylists($userId) {

		try {
			$api["response"]["get"] = $this->client->request("GET", "/".$this->version."/objects/".$userId."/playlists", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["get"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"]["playlists"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function addPlaylist($userId) {

		try {
			$api["response"]["post"] = $this->client->request("POST", "/".$this->version."/objects/".$userId."/playlists", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["post"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function deletePlaylist($userId, $playlistId) {
		
		try {
			$api["response"]["delete"] = $this->client->request("DELETE", "/".$this->version."/objects/".$userId."/playlists/".$playlistId, [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["delete"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return true;

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	// genres = 0 | 1
	// tv_guide =  0 | 1
	// available_mode = 0 | 1
	public function setPlaylistOptions($userId, $playlistId, $options) {

		try {
			$api["response"]["put"] = $this->client->request("PUT", "/".$this->version."/objects/".$userId."/playlists/".$playlistId, [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											'json' => [
													'comment' => $options['comment'],
													'genres' => (($options['genres']) ? $options['genres'] : 0),
													'tv_guide' => (($options['tv_guide']) ? $options['tv_guide'] : 0),
													'available_mode' => (($options['available_mode']) ? $options['available_mode'] : 0),
												]
										]);

			$api["response"]["body"] = $api["response"]["put"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function getServices() {

		try {
			$api["response"]["get"] = $this->client->request("GET", "/".$this->version."/search/bundles", [
											// "debug" => true,
											'headers' => [
													'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["get"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"]["bundles"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	public function getChannels($serviceId) {

		try {
			$api["response"]["get"] = $this->client->request("GET", "/".$this->version."/bundles/".$serviceId, [
											// "debug" => true,
											'headers' => [
													// 'Authorization' => 'Bearer '.(($this->token) ? $this->token : $this->getToken()),
												],
											// 'json' => [
											// 	]
										]);

			$api["response"]["body"] = $api["response"]["get"]->getBody();
			$api["response"]["data"] = json_decode($api["response"]["body"], true);

			return $api["response"]["data"]["channels"];

		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
			$this->logger->error("Exception caught", $e->getTrace());

			return false;
		}

	}

	// Добавляем платеж в очередь
	public function addLocalPayment($payment, $account = 'internet', $userId) {
		global $db;

		if (!empty($payment) && !empty($account) && !empty($userId)) {
            $db->query("INSERT INTO task_payments (
                    created_by,
                    created_id,
                    account,
                    account_id,
                    details,
                    method,
                    amount,
                    created_at,
                    deferred_at,
                    updated_at
                ) VALUES (
                    'prostotv',
                    '".$payment["created_id"]."',
                    '".$account."',
                    '".$userId."',
                    '".$payment["details"]."',
                    '".$payment["method"]."',
                    '".$payment["amount"]."',
                    '".$this->apptime."',
                    '".$payment["deferred"]."',
                    '".$this->apptime."'
                )");
            return true;
        } else {
        	$this->logger->error("Не удалось добавить в очередь платеж");
        	$this->logger->error("PAYMENT='".implode(", ",$payment)."', ACCOUNT='".$account."', ACCOUNT_ID='".$userId."'");
        	return false;
        }
	}

	// Обновляем платеж в очереди
	public function updateLocalPayment($update, $id) {
		global $db;

		if (!empty($update) && !empty($id)) {
			$db->query("UPDATE task_payments SET ".implode(', ', $update)." WHERE id = '".$id."'");
            return true;
        } else {
        	$this->logger->error("Не удалось обновить платеж в очереди");
        	$this->logger->error("UPDATE='".implode(", ",$update)."', ID='".$id."'");
        	return false;
        }
	}

	// Получаем отложенный платеж
	public function getLocalPayment($userId, $tariffId, $account = 'internet') {
		global $db;

		return $db->super_query("SELECT id, updated_at FROM task_payments WHERE created_by = 'prostotv' AND created_id = '".$tariffId."' AND account = '".$account."' AND account_id = '".$userId."' AND status = 'processing' AND deferred_at > 0");
	}

	// Добавляем подписку
	public function addLocalSubscribe($userId, $tariff) {
		global $db;

		if (!empty($userId) && !empty($tariff["id_tariff"])) {

			$subscribeId = $this->getLocalMainSubscribe($userId)["id_subscribe"];

			if ($tariff["type"] == 'main' && !empty($subscribeId)) {

				$this->updateLocalSubscribe($subscribeId, $tariff["id_tariff"]);

			} else {

				$db->query("INSERT LOW_PRIORITY INTO prostotv_subscribes (
	                    id_user,
	                    id_tariff,
	                    signed,
	                    time_last_state,
	                    time_last_activation,
	                    time_next_removal,
	                    time_add
	                ) VALUES (
	                    '".$userId."',
	                    '".$tariff["id_tariff"]."',
	                    'yes',
	                    '".$this->apptime."',
	                    '".$this->apptime."',
	                    '".$this->time_next_removal."',
	                    '".$this->apptime."'
	                )");
			}

            return true;

        } else {
        	$this->logger->error("Не удалось добавить подписку");
        	$this->logger->error("ID_TARIFF='".$tariff["id_tariff"]."', ID_USER='".$userId."'");
        	return false;
        }
	}

	// Получаем подписки
	public function getLocalSubscribes($userId) {
		global $db;

		return $db->super_query("SELECT ps.id AS id_subscribe, ps.id_tariff, ps.signed, pt.id_service, pt.type, pt.cost, pt.cashback, pt.title FROM prostotv_subscribes AS ps LEFT JOIN prostotv_tariffs AS pt ON ps.id_tariff = pt.id WHERE ps.id_user = '".$userId."'", true);
	}

	// Получаем указанную подписку
	public function getLocalSubscribe($userId, $tariffId) {
		global $db;

		return $db->super_query("SELECT id AS id_subscribe, signed FROM prostotv_subscribes WHERE id_user = '".$userId."' AND id_tariff = '".$tariffId."'");
	}

	// Обновляем подписку
	public function updateLocalSubscribe($subscribeId, $tariffId) {
		global $db;

		$update = array();

		$update[] = "id_tariff = '".$tariffId."'";
        $update[] = "signed = 'yes'";
        $update[] = "time_last_state = '".$this->apptime."'";
        $update[] = "time_last_activation = '".$this->apptime."'";
        $update[] = "time_next_removal = '".$this->time_next_removal."'";

		if (!empty($subscribeId) && !empty($tariffId)) {
			$db->query("UPDATE prostotv_subscribes SET ".implode(', ', $update)." WHERE id = '".$subscribeId."'");
            return true;
        } else {
        	$this->logger->error("Не удалось обновить подписку");
        	$this->logger->error("TARIFF_ID='".$tariffId."', SUBSCRIBE_ID='".$subscribeId."'");
        	return false;
        }
	}

	// Выключаем подписку
	public function disableLocalSubscribe($subscribeId) {
		global $db;

		$update = array();

		$update[] = "signed = 'no'";
        $update[] = "time_last_state = '".$this->apptime."'";
        $update[] = "time_next_removal = '0'";

		if (!empty($subscribeId)) {
			$db->query("UPDATE prostotv_subscribes SET ".implode(', ', $update)." WHERE id = '".$subscribeId."'");
            return true;
        } else {
        	$this->logger->error("Не удалось отключить подписку");
        	$this->logger->error("ID='".$subscribeId."'");
        	return false;
        }
	}

	// Удаляем подписку
	public function deleteLocalSubscribe($subscribeId) {
		global $db;

		if (!empty($subscribeId)) {
			$db->query("DELETE LOW_PRIORITY FROM prostotv_subscribes WHERE id = '".$subscribeId."'");
			return true;
        } else {
        	$this->logger->error("Не удалось удалить подписку");
        	$this->logger->error("ID='".$subscribeId."'");
        	return false;
        }
	}

	// Добавляем клиента
	public function addLocalUser($user, $userAccountId) {
		global $db;

		if (!empty($user)) {
			$db->query("INSERT LOW_PRIORITY INTO prostotv_users (
                    id_user,
                    id_prostotv,
                    passwd,
                    time_add
                ) VALUES (
                    '".$userAccountId."',
                    '".$user["id"]."',
                    '".$user["password"]."',
                    '".$this->apptime."'
                )");
            return true;
        } else {
        	$this->logger->error("Не удалось добавить клиента");
        	$this->logger->error("USER='".implode(", ",$user)."', ID_USER='".$userAccountId."'");
        	return false;
        }
	}

	// Получаем клиента
	public function getLocalUser($userId) {
		global $db;

		return $db->super_query("SELECT id_prostotv, passwd, auto_renewal FROM prostotv_users WHERE id_user = '".$userId."'");
	}

	// Получаем тарифы
	public function getLocalTariffs() {
		global $db;

		return $db->super_query("SELECT id AS id_tariff, id_service, type, title, icon, cost, cashback, popular FROM prostotv_tariffs WHERE state = 'on' ORDER BY sorting ASC", true);
	}

	// Получаем выбранный тариф
	public function getLocalSelectedTariff($tariffId) {
		global $db;

		return $db->super_query("SELECT id AS id_tariff, id_service, type, cost, cashback, title FROM prostotv_tariffs WHERE id = '".$tariffId."'");
	}

	// Получаем текущий базовый тариф
	public function getLocalMainSubscribe($userId) {
		global $db;

		return $db->super_query("SELECT ps.id AS id_subscribe, pt.id AS id_tariff, pt.id_service, pt.cost, pt.cashback, pt.title FROM prostotv_subscribes AS ps LEFT JOIN prostotv_tariffs AS pt ON ps.id_tariff = pt.id WHERE ps.id_user = '".$userId."' AND pt.type = 'main'
            ");
	}

	// Добавляем уведомление
	public function addLocalNotice($notice, $userId, $tariffId) {
		global $db;

		if (!empty($notice) && !empty($userId) && isset($tariffId)) {
			$db->query("INSERT INTO log_notice (
                    id_user_account,
                    created,
                    id_created,
                    method,
                    notice, 
                    time_add 
                ) VALUES (
                    '".$userId."',
                    'prostotv',
                    '".$tariffId."',
                    '".$notice["method"]."',
                    '".$notice["text"]."',
                    '".$this->apptime."'
                )");
            return true;
        } else {
        	$this->logger->error("Не удалось добавить уведомление");
        	$this->logger->error("NOTICE='".implode(", ",$notice)."', ID_USER_ACCOUNT='".$userId."', ID_CREATED='".$tariffId."'");
        	return false;
        }
	}

	// Списываем деньги
	public function writeOffMoney($tariff, $userId, $deffered = 0) {
		global $db;

		if (!empty($userId) && !empty($tariff["id_tariff"]) && !empty($tariff["cost"])) {

			$payment = array();

			$payment["created_id"] = $tariff["id_tariff"];
	        $payment["details"] = "Оплата услуги PROSTOTV пакет ".mb_strtoupper($tariff["title"]);
	        $payment["method"] = "down";
	        $payment["amount"] = -1 * round($tariff["cost"] / date('t') * (date('t') - date('d') + 1), 5, PHP_ROUND_HALF_DOWN);
	        $payment["deferred"] = $deffered;

	        $this->addLocalPayment($payment, 'internet', $userId);

			return true;

		} else {
        	$this->logger->error("Не удалось списать средства");
        	$this->logger->error("ID_USER_ACCOUNT='".$userId."', TARIFF_ID='".$tariff["id_tariff"]."'");
        	return false;
        }
	}

	// Возвращаем деньги за остаток непросмотренных дней
	public function refundMoney($userId, $tariff, $deffered = 0) {
		global $db;

		if (!empty($userId) && !empty($tariff["id_tariff"]) && !empty($tariff["cost"])) {

			$payment = array();

			$payment["created_id"] = $tariff["id_tariff"];
	        $payment["details"] = "Возврат средств за остаток дней пакета ".mb_strtoupper($tariff["title"]);
	        $payment["method"] = "up";
	        $payment["amount"] = round($tariff["cost"] / date('t') * ( date('t') - date('d') ), 5, PHP_ROUND_HALF_DOWN);
	        $payment["deferred"] = $deffered;

	        $this->addLocalPayment($payment, 'internet', $userId);

			return true;

		} else {
        	$this->logger->error("Не удалось сделать возврат средств");
        	$this->logger->error("ID_USER_ACCOUNT='".$userId."', TARIFF_ID='".$tariff["id_tariff"]."'");
        	return false;
        }
	}

	// Начисляем кешбэк
	public function addCashback($tariff, $userId, $deffered = 0) {
		global $db;

		$payment = array();

		$payment["created_id"] = $tariff["id_tariff"];
        $payment["details"] = "Начисление средств на кешбэк-счет за пакет ".mb_strtoupper($tariff["title"]);
        $payment["method"] = "up";
        $payment["amount"] = round($tariff["cashback"] / date('t') * (date('t') - date('d') + 1), 5, PHP_ROUND_HALF_DOWN);
        $payment["deferred"] = $deffered;

        $this->addLocalPayment($payment, 'cashback', $userId);

		return true;
	}

	// Пересчитываем кешбэк за просмотренные дни и отпускаем в начисление
	public function cancelCashback($userId, $tariff) {
		global $db;

		if (!empty($userId) && !empty($tariff["id_tariff"]) && !empty($tariff["cashback"])) {

			$update = array();

			$payment = $this->getLocalPayment($userId, $tariff["id_tariff"], 'cashback');

	        if (!empty($payment)) {

	            $update[] = "amount = '".round($tariff["cashback"] / date('t') * (date('d') - date('d',$payment['updated_at'])), 5, PHP_ROUND_HALF_DOWN)."'";
	            $update[] = "updated_at = '".$this->apptime."'";
	            $update[] = "deferred_at = '0'";

	            $this->updateLocalPayment($update, $payment["id"]);

	            return true;
	        } else {
	        	$this->logger->error("Не удалось найти кэшбек-платеж в очереди платежей");
	        	$this->logger->error("USER_ID='".$userId."', TARIFF_ID='".$tariff["id_tariff"]."', COST='".$tariff["cost"]."'");
	        	return false;
	        }
        } else {
        	$this->logger->error("Не удалось пересчитать кэшбек-платеж в очереди платежей");
        	$this->logger->error("USER_ID='".$userId."', TARIFF_ID='".$tariff["id_tariff"]."', COST='".$tariff["cost"]."'");
        	return false;
        }
	}

	// Обновляем пароль
	public function updateLocalPassword($userId, $passwd) {
		global $db;

		$update = array();

		$update[] = "passwd = '".$passwd."'";

		if (!empty($userId) && !empty($passwd)) {
			$db->query("UPDATE prostotv_users SET ".implode(', ', $update)." WHERE id_user = '".$userId."'");
            return true;
        } else {
        	$this->logger->error("Не удалось изменить пароль");
        	$this->logger->error("USER_ID='".$userId."', PASSWD='".$passwd."'");
        	return false;
        }
	}

	// Обновляем автопродление
	public function updateLocalAutorenewal($userId, $auto) {
		global $db;

		$update = array();

		$update[] = "auto_renewal = '".$auto."'";

		if (!empty($userId) && !empty($auto)) {
			$db->query("UPDATE prostotv_users SET ".implode(', ', $update)." WHERE id_user = '".$userId."'");
            return true;
        } else {
        	$this->logger->error("Не удалось изменить автопродление");
        	$this->logger->error("USER_ID='".$userId."', AUTO='".$auto."'");
        	return false;
        }
	}

	// Получаем уведомления
	public function getLocalNotices($userId) {
		global $db;

		return $db->super_query("SELECT notice, time_add FROM log_notice WHERE id_user_account = '".$userId."' AND created = 'prostotv' ORDER BY id DESC LIMIT 15", true);
	}


}
