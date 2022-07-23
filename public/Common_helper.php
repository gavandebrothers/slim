<?php  
/* Generate OTP */
if (!function_exists('geneOTP')) {
	function geneOTP($digits=6) {
		$generator = "135792468"; 
		$result = "";  
		for ($i = 1; $i <= $digits; $i++) { 
			//$result .= substr($generator, (mt_rand()%(strlen($generator))), 1); 
			$result .= $generator[(mt_rand() % strlen($generator))];
		} 
		return $result; 
	}
}
?>