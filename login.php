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
	$agent = $result->agent;
	//$device_id = $result->device_id;
	$ig_sig_key = $result->ig_sig_key;
	$sig_key_version = $result->sig_key_version;
	if($result->error == false){
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
			
			$Login = Login($uig,$pig,$agent,$ig_sig_key,$sig_key_version);
			if($Login['status'] == "ok"){
				$pk = $Login['logged_in_user']['pk'];
				$F = file_get_contents('data.txt');
				$F = urlencode($F);
				echo "\n";
				
				$data2 = [
						'status'            => 'utama',
						'username'          => $ugc,
						'usernameig'            => $uig,
						'pk'				=> $pk,
						'data'				=> $F,
					];
				$result = Submit('http://gramcaster.com/app/v3/IPA.php',$data2);
				$result = json_decode($result);
				if($result->error == false){
					echo $result->pesan;
					echo "\n";
				}else{
					echo $result->pesan_error;
					echo "\n";
				}
			}else{
				echo $Login['message'];
				echo "\n";
			}
		}else if($pilihan == '2'){
			echo "\n";
			echo "Menambahkan Akun Arisan\n";
			echo "Username Instagram Arisan Anda : ";
			$uig=trim(fgets(STDIN));
			echo "Password Instagram Arisan Anda : ";
			$pig=trim(fgets(STDIN));
			echo "Kelompok Arisan Anda (Masukkan Antara Angka 1 Sampai 5) : ";
			$kelompok=trim(fgets(STDIN));
			if($kelompok > 5 and $kelompok < 1){
				echo "Salah Memasukkan Kelompok\n";
			}else{
				$Login = Login($uig,$pig,$agent,$ig_sig_key,$sig_key_version);
				if($Login['status'] == "ok"){
					$pk = $Login['logged_in_user']['pk'];
					$F = file_get_contents('data.txt');
					$F = urlencode($F);
					echo "\n";
					
					$data2 = [
							'status'            => 'arisan',
							'username'          => $ugc,
							'usernameig'            => $uig,
							'pk'				=> $pk,
							'data'				=> $F,
							'kelompok'			=> $kelompok,
						];
					$result = Submit('http://gramcaster.com/app/v3/IPA.php',$data2);
					$result = json_decode($result);
					if($result->error == false){
						echo $result->pesan;
						echo "\n";
					}else{
						echo $result->pesan_error;
						echo "\n";
					}
				}else{
					echo $Login['message'];
					echo "\n";
				}
			}
		}else{
			echo 'Pilihan Salah';
			echo "\n";
		}
	}else{
		echo $result->pesan_error;
		echo "\n";
	}
	
	function Login($username,$password,$agent,$ig_sig_key,$sig_key_version){
		$device_id = generateDeviceId(md5($username.$password));
		$uuid = GenerateGuid(true);
		
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
		$login = request('accounts/login/', $username, $agent, generateSignature(json_encode($data),$ig_sig_key,$sig_key_version));
		return $login[1];
	}
	function generateDeviceId($seed)
    {
        $volatile_seed = filemtime(__DIR__);
        return 'android-'.substr(md5($seed.$volatile_seed), 16);
    }
	function generateSignature($data,$ig_sig_key,$sig_key_version)
    {
        $hash = hash_hmac('sha256', $data, $ig_sig_key);

        return 'ig_sig_key_version='.$sig_key_version.'&signed_body='.$hash.'.'.urlencode($data);
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
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
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
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.68 Safari/534.24');
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