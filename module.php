<?PHP

	$module = nrns::module("language", ['fs']);

	$module->config(function(){
	
		require 'provider/languageProvider.php';
		
		
	});

	$module->provider('languageProvider', "language\\languageProvider");
	
	
	
?>