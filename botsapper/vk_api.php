<?php
declare(strict_types = 1);

$hidden = false;
$clear = false;
$buttons = [];
$count_buttons = 0;
function set_hidden() {
    global $hidden;
    $hidden = true;
}

function set_clear(bool $status = true) {
    global $clear;
    $clear = $status;
    $clear = $status;
}

function buttons_unset() {
    global $buttons;
    $buttons = [];
}

/**
 * @return bool|false|string
 */
function get_buttons() {
    global $hidden, $buttons, $clear;
    if ($clear) {
        return json_encode(['one_time' => true, 'buttons' => []], JSON_UNESCAPED_UNICODE);
    } elseif (count($buttons) > 0) {
        return json_encode(['one_time' => $hidden, 'buttons' => $buttons], JSON_UNESCAPED_UNICODE);
    } else return false;
}

/**
 * @param string $type
 * @param array  $payload
 * @param string $label
 * @param string $color
 * @param int    $line
 */
function set_buttons($payload = [], string $label = 'text', string $color = 'negative', int $line = 0, string $type = 'text') {
    global $count_buttons, $buttons;
    ++$count_buttons;
    $buttons[$line][] = ['action' => ['type' => $type, 'payload' => json_encode($payload ? $payload : ["button" => $count_buttons]), 'label' => $label], 'color' => $color];
}


/**
 * @param $request
 *
 * @throws Exception
 */
function vk_user_info(&$request) {
    $request['user'] = user_info($request['object']['from_id']);
}


/**
 * @param $user_id
 *
 * @return mixed
 * @throws Exception
 */
function user_info($user_id) {
    $user_info = vk_api_call('users.get', ['user_ids' => $user_id, 'fields' => ['first_name']]);
    return max($user_info);
}

/**
 * @param int    $peer_id
 * @param string $message
 * @param array  $attachment
 *
 * @param string $forward_messages
 *
 * @return bool|mixed
 * @throws Exception
 */
function vk_send(int $peer_id, string $message, array $attachment = [], $forward_messages = '') {
    $message = empty($message) ? 'Твое сообщение не распознано!' : $message;
    if ($message != '' || !empty($forward_messages)) {
        $request = ['peer_id' => $peer_id, 'random_id' => rand(0, 2000000), 'message' => $message, 'attachment' => $attachment ? implode(',', $attachment) : false];
        $keyboard = get_buttons();
        if ($keyboard) $request['keyboard'] = $keyboard;
        if ($forward_messages) $request['forward_messages'] = $forward_messages;

        return vk_api_call('messages.send', $request);
    }
    return false;
}


/**
 * @param string $method
 * @param array  $params
 *
 * @return mixed
 * @throws Exception
 */
function vk_api_call(string $method, array $params = []) {
    $curl_init = curl_init(HOST_VKAPI . $method);
    curl_setopt_array($curl_init, [CURLOPT_HEADER => true, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query(format_params(array_merge(['v' => '5.95', 'access_token' => ACCESS_TOKEN], $params)))]);
    $response = curl_exec($curl_init);
    $curl_error_code = curl_errno($curl_init);
    $curl_error = curl_error($curl_init);
    curl_close($curl_init);
    if ($curl_error || $curl_error_code) {
        $error_msg = "Failed curl request. Curl error {$curl_error_code}";
        if ($curl_error) {
            $error_msg .= ": {$curl_error}";
        }
        $error_msg .= '.';
        throw new Exception($error_msg);
    }

    $parts = explode("\r\n\r\n", $response);
    $raw_body = array_pop($parts);
    $decoded_body = json_decode(trim($raw_body), true);

    if (isset($decoded_body['error'])) throw new Exception($decoded_body['error']['error_msg'] . " " . json_encode($decoded_body['error']['request_params'], (int)$decoded_body['error']['error_code']));
    return $decoded_body['response'];
}

/**
 * @param array $params
 *
 * @return array
 */
function format_params(array $params) {
    foreach ($params as $key_param => $value) {
        if (is_array($value)) {
            if ((count($value) - count($value, COUNT_RECURSIVE)) < 0) {
                $params[$key_param] = $value;
            } else {
                $params[$key_param] = implode(',', $value);
            }
        } elseif (is_bool($value)) {
            $params[$key_param] = $value ? 1 : 0;
        }
    }
    return $params;
}

/**
 * @param string $method
 * @param array  $parameters
 *
 * @return array|bool
 */
function coin_request(string $method, array $parameters) {
    $parameters['access_token'] = COIN_API_KEY;
    return coin_request_api($method, json_encode($parameters, JSON_UNESCAPED_UNICODE));
}


/**
 * @param string $method
 * @param string $body
 *
 * @return array|bool
 */
function coin_request_api(string $method, string $body) {

    if (extension_loaded('curl')) {
        $curl_init = curl_init();
        curl_setopt_array($curl_init, [CURLOPT_TIMEOUT => 2, CURLOPT_URL => COIN_API_HOST . $method, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
        file_put_contents('dsfsdfdfd',COIN_API_HOST . '/' . $method . '/');
        $response = curl_exec($curl_init);
        $error = curl_error($curl_init);

        curl_close($curl_init);
        if ($error) {
            return ['status' => false, 'error' => $error];
        } else {
            $response = json_decode($response, true);
            return $response;
        }
    }

    return false;
}


/**
 * @param int $user_id
 * @param int $amount
 *
 * @return array|bool
 */
function coin_send(int $user_id, int $amount = 0) {
    $params = [];

    $params['user_id'] = $user_id;
    $commission = COMMISSION;
    $amount = ($amount - (($amount / 100) * $commission));
    $params['sum'] = $amount <= 0 ? 0.001 : $amount;

    return coin_request('transfer', $params);
}


/**
 * @return array|bool
 */
function info() {
    return coin_request('info', []);
}
