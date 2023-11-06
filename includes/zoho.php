<?php


if (! function_exists('dd')) {
    function dd($data)
    {
        echo "<pre>";
        var_dump($data);die;
    }
}
if (! function_exists('zAuth')) {
    function zAuth($config)
    {
       

        if (isset($config['refresh_token'])) {
            return zRefreshToken($config);
        } else {
          
            return zAccessToken($config);
        }
    }
}


if (! function_exists('zAccessToken')) {
    function zAccessToken($config)
    {

        $postInput = [
            'client_id' => (isset($config['client_id']))?$config['client_id']:null,
            'client_secret' => (isset($config['client_secret']))?$config['client_secret']:null,
            'code' => (isset($config['code']))?$config['code']:null,
            'redirect_uri' => (isset($config['redirect_uri']))?urldecode($config['redirect_uri']):null,
            'grant_type' => 'authorization_code',
        ];
      
        $url = $config['accounts_url'].'/oauth/v2/token';
        // dd($postInput);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postInput));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $responseBody = json_decode($response, true);


        if (isset($responseBody['refresh_token']) && isset($responseBody['access_token'])) {
            $resutl['refresh_token'] = $responseBody['refresh_token'];
            $resutl['access_token'] = $responseBody['access_token'];
            $resutl['expires_in'] = time()+$responseBody['expires_in']-60;

            return $resutl;
        } else {
            dd($responseBody);

            return false;
        }
    }
}
if (! function_exists('zRefreshToken')) {
    function zRefreshToken($config = null)
    {

        $postInput = [
            'client_id' => $config->zoho_client_id,
            'client_secret' => $config->zoho_client_secret,
            'refresh_token' => $config->refresh_token,
            'grant_type' => 'refresh_token',
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', config('zoho.auth_url').'/token', ['form_params' => $postInput]);
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);

        if (isset($responseBody['access_token'])) {
            $config->access_token = $responseBody['access_token'];
            $config->expires_in = $responseBody['expires_in'];
            $config->save();
            cache()->forget('setting');

            return cache()->remember('setting', ($responseBody['expires_in'] - 60), function () use ($config) {
                return $config;
            });
        } else {
            return false;
        }
    }
}
if (! function_exists('zohoGet')) {
    function zohoGet($url, $quary = [], $response = 'data')
    {
        $setting = zAuth();

        $apiURL = config('zoho.api_url').$url;

        $headers = [
            'Authorization' => 'Zoho-oauthtoken '.$setting->access_token,
            'Content-Type' => 'application/json',
        ];
        // dump($apiURL);
        $res = Http::withHeaders($headers)->get($apiURL, $quary);
        $statusCode = $res->status();
        $responseBody = json_decode($res->getBody(), true);

        $resutl = isset($responseBody[$response]) ? $responseBody[$response] : null;

        return $resutl;
    }
}

if (! function_exists('zohoPost')) {
    function zohoPost($url, $data = [])
    {

        $setting = zAuth();
        $apiURL = config('zoho.api_url').$url;

        $headers = [
            'Authorization' => 'Zoho-oauthtoken '.$setting->access_token,
            'Content-Type' => 'application/json',
        ];

        $inputData = [
            'data' => [$data],
            'trigger' => [
                'workflow',
            ],
        ];
        //dd($inputData);

        $response = Http::withHeaders($headers)->post($apiURL, $inputData);
        $statusCode = $response->status();
        $responseBody = json_decode($response->getBody(), true);

        return $responseBody;
    }
}
if (! function_exists('zohoUpload')) {
    function zohoUpload($file)
    {
        $setting = zAuth();
        $fileName = $file->getClientOriginalName();
        $filePath = $file->getPathname();
        $apiURL = config('zoho.api_url').'/files';
        $headers = [
            'Authorization' => 'Zoho-oauthtoken '.$setting->access_token,

        ];
        $options = [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => file_get_contents($filePath),
                    'filename' => $fileName,
                    'headers' => [
                        'Content-Type' => 'multipart/form-data',
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders($headers)
            ->attach($options['multipart'])
            ->post($apiURL);

        if ($response->successful()) {
            return response()->json(json_decode($response->body(), true), 200);
        } else {
            return response()->json(['error' => $response->json()], 400);
        }
    }
}

if (! function_exists('zohoDownload')) {
    function zohoDownload($file_id, $extenion)
    {
        $setting = zAuth();
        $apiURL = config('zoho.api_url').'/files';

        $quary = ['id' => $file_id];

        $headers = [
            'Authorization' => 'Zoho-oauthtoken '.$setting->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW',

        ];
        // dump($apiURL);
        $response = Http::withHeaders($headers)->get($apiURL, $quary);

        if ($response->ok()) {
            // File downloaded successfully
            $content = $response->body();

            return response($content, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="file.'.$extenion.'"',
            ]);
        } else {
            // Handle HTTP error response
            $statusCode = $response->status();
            $errorMessage = $response->body();

            return response($errorMessage, $statusCode);
        }
    }
}
if (! function_exists('zResponseAuth')) {
    function zResponseAuth($res)
    {
        if (isset($res['error'])) {
            $errors = [];
            if ($res['error'] == 'invalid_client') {
                $errors['client_id'] = [$res['error']];
            }
            if ($res['error'] == 'invalid_code') {
                $errors['code'] = [$res['error']];
            }
            if ($res['error'] == 'invalid_client_secret') {
                $errors['client_secret'] = [$res['error']];
            }
            if (! empty($errors)) {

                return response()->json($errors, 422);
            } else {
                return response()->json($res, 422);
            }

        } else {

            $setting = Setting::first();
            if ($setting) {
                if (isset($res['access_token'])) {
                    $setting->access_token = $res['access_token'];
                }
                if (isset($res['refresh_token'])) {
                    $setting->refresh_token = $res['refresh_token'];
                }
                if (isset($res['expires_in'])) {
                    $setting->expires_in = time() + $res['expires_in'] - 60;
                }
                $setting->save();
            }

            return response()->json($res, 200);
        }

    }
}