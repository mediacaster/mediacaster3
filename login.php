<?php	
	//$data = Login('roniisuryadii','Roni17081995');
	//print_r($data);
	echo "Gramcaster Login\n";
	echo "Username Gramcaster Anda : ";
	$ugc=trim(fgets(STDIN));
	echo "Password Gramcaster Anda : ";
	$pgc=trim(fgets(STDIN));
	echo "Loading ... \n";
	$data = [
			'status'            => 'login',
			'username'          => $ugc,
			'password'            => $pgc,
		];
	$result = Submit('http://gramcaster.com/app/v3/IPA.php',$data);
	$result = json_decode($result);
	if(!$result->error){
		echo $result->pesan;
		echo "\n";
		echo "1. Tambah Akun Utama\n";
		echo "2. Tambah Akun Arisan\n";
		echo "Pilihan : ";
		$pilihan=trim(fgets(STDIN));
		if($pilihan == '1'){
			echo "\n";
			echo "Menambahkan Akun Utama\n";
			echo "Username Instagram Anda : ";
			$uig=trim(fgets(STDIN));
			echo "Password Instagram Anda : ";
			$pig=trim(fgets(STDIN));
			$Login = Login($uig,$pig);
			if($Login['status'] == "ok"){
				//$response["error"] = FALSE;
				$pk = $Login['logged_in_user']['pk'];
				$F = file_get_contents('data.txt');
				//echo $F;
				echo "\n";
				
				$data = [
						'status'            => 'utama',
						'username'          => $ugc,
						'usernameig'            => $uig,
						'pk'				=> $pk,
						'data'				=> $F,
					];
				$result = Submit('http://gramcaster.com/app/v3/IPA.php',$data);
				$result = json_decode($result);
				print_r($result);
				$response["pesan"] = "Berhasil Menambahkan Akun $username";
				echo json_encode($response);
				echo "\n";
			}else{
				//$response["error"] = TRUE;
				$response["pesan_error"] = $Login['message'];
				//$response["error_type"] = $Login['error_type'];
				echo json_encode($response);
			}
		}else if($pilihan == '2'){
			
		}else{
			echo 'Pilihan Salah';
		}
	}else{
		echo $result->pesan_error;
		echo "\n";
	}
	
	function Login($username,$password){
		$device_id = generateDeviceId(md5($username.$password));
		$uuid = GenerateGuid(true);
		$agent = 'Instagram 12.0.0.7.91 Android (18/4.3; 320dpi; 720x1280; Xiaomi; HM 1SW; armani; qcom; en_US';
		
		$fetch = request('si/fetch_headers/?challenge_type=signup&guid='.GenerateGuid(false), $username, $agent, null);
        preg_match('#Set-Cookie: csrftoken=([^;]+)#', $fetch[0], $token);
		 
		$data = [
			'phone_id'            => GenerateGuid(true),
			'_csrftoken'          => $token[0],
			'username'            => $username,
			'guid'                => $uuid,
			'device_id'           => $device_id,
			'password'            => $password,
			'login_attempt_count' => '0',
		];		
		$login = request('accounts/login/', $username, $agent, generateSignature(json_encode($data)));
		return $login[1];
	}
	
	function generateSignature($data)
    {
        $hash = hash_hmac('sha256', $data, '68a04945eb02970e2e8d15266fc256f7295da123e123f44b88f09d594a5902df');

        return 'ig_sig_key_version='.'4'.'&signed_body='.$hash.'.'.urlencode($data);
    }
	function GenerateGuid($type)
    {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

        return $type ? $uuid : str_replace('-', '', $uuid);
    }
	function generateDeviceId($seed)
    {
        $volatile_seed = filemtime(__DIR__);
        return 'android-'.substr(md5($seed.$volatile_seed), 16);
    }
	function request($endpoint, $userig, $agent, $post = null)
    {
        $headers = [
			'Connection: close',
			'Accept: */*',
			'Content-type: application/x-www-form-urlencoded; charset=UTF-8',
			'Cookie2: $Version=1',
			'Accept-Language: en-US',
		];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://i.instagram.com/api/v1/'.$endpoint);
		curl_setopt($ch, CURLOPT_USERAGENT,'Instagram 12.0.0.7.91 Android (18/4.3; 320dpi; 720x1280; Xiaomi; HM 1SW; armani; qcom; en_US');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "data.txt");
		curl_setopt($ch, CURLOPT_COOKIEJAR, "data.txt");
		
		
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $body = substr($resp, $header_len);

        curl_close($ch);

        return [$header, json_decode($body, true)];
    }
    
	function Submit($url,$fields)
    {
		$field_string = http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);
		curl_setopt($ch,CURLOPT_REFERER,$url);          
		curl_setopt($ch,CURLOPT_TIMEOUT,5);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT,'Opera/9.80 (Android; Opera Mini/7.6.40234/37.7148; U; id) Presto/2.12.423 Version/12.16');
		curl_setopt($ch,CURLOPT_COOKIEFILE,'login.txt');
		curl_setopt($ch,CURLOPT_COOKIEJAR,'login.txt');
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$field_string);   
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$body = curl_exec($ch);
		return $body;
		curl_close($ch);
    }

?>