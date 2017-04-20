<?php
/*
 * PHP Freaks Code Library
 * http://www.phpfreaks.com/quickcode.php
 *
 * Title: Sniff a Client Browser and OS
 * Version: 1.0
 * Author: Will Chapman aka(elitecodex)
 * Date: Tuesday, 08/27/2002 - 01:08 PM
 *
 * 
 *
 * NOTICE: This code is available from PHPFreaks.com code Library.
 *         This code is not Copyrighted by PHP Freaks. 
 *
 *         PHP Freaks does not claim authorship of this code.
 *
 *         This code was submitted to our website by a user. 
 *
 *         The user may or may not claim authorship of this code.
 *
 *         If there are any questions about the origin of this code,
 *         please contact the person who submitted it, not PHPFreaks.com!
 *
 *         USE THIS CODE AT YOUR OWN RISK! NO GUARANTEES ARE GIVEN!
 *
 * SHAMELESS PLUG: Need WebHosting? Checkout WebHost Freaks:
 *                 http://www.webhostfreaks.com
 *                 WebHosting by PHP Freaks / The Web Freaks!
*/

// *    Description / Example:
// *    
// *    This is a class which will detect what browser the client is using and what operating system.  It will not detect all of them, but this can easily be edited to add new os/browsers.  Keep it free!  :)

// Enhacements by CRCR, 2008

class client_browser_info
{
	public $platform = '', $browser = '', $menor = 0, $mayor = 0, $nombre = '', $agente = '';

	function client_browser_info()
	{
		$this->agente = $_SERVER['HTTP_USER_AGENT'];
		
  		$this->platform = "Unknown";
		// Determine the platform they are on
		if (strstr($this->agente,'Win')) $this->platform='Windows';
		else if (strstr($this->agente,'Mac')) $this->platform='Macintosh';
		else if (strstr($this->agente,'Linux')) $this->platform='Linux';
		else if (strstr($this->agente,'Unix')) $this->platform='Unix';
		else $this->platform = 'Other';
    		
		// Next, determine the browser they are using		
		if (preg_match("/Chrome\/([0-9]+\.[0-9]+)/i", $this->agente, $found))
		{
		 	$this->browser = "Chrome " . $found[1];
		}
		elseif ( preg_match("/Opera ([0-9]\.[0-9]{0,2})/i", $this->agente, $found) &&
			   strstr($this->agente, "MSIE") )
		{
			// This will identify the Opera browser even when it tries to ID itself
			// as MSIE 5.0			
		 	$this->browser = "Opera " . $found[1];
		}
		else if ( preg_match("/Opera ([0-9]\.[0-9]{0,2})/i", $this->agente, $found) &&
				 strstr($this->agente, "Mozilla") )
		{
		  	// Finds Opera if ID's itself as Mozilla based browser
		 	$this->browser = "Opera " . $found[1];
		}
		else if ( preg_match("/Opera\/([0-9]\.[0-9]{0,2})/i", $this->agente, $found) )
		{
		  // Finds Opera when ID'ing itself as Opera
		  $this->browser = "Opera " . $found[1];
		}
    	else if ( preg_match("/Netscape[0-9]\/([0-9]{1,2}\.[0-9]{1,2})/i", $this->agente, $found) )
    	{
		  	// For Netscape 6.x
    		$this->browser = "Netscape " . $found[1];
    	}
		else if ( preg_match("/Mozilla\/([0-9]{1}\.[0-9]{1,2}) \[en\]/i", $this->agente,$found) )
    	{
			// For Netscape 4.x
    		$this->browser = "Netscape " . $found[1];
    	}		
    	else if ( preg_match("/MSIE ([0-9]{1,2})/i", $this->agente, $found) )
    	{
			// For MSIE
    		$this->browser = $found[0];
    	}    
		else
		    $this->browser = $this->agente;

		$this->menor = 0;
		$this->mayor = 0;
		$this->nombre = '';
		if (($ss = strtok($this->browser, " "))!==false)
		{
		    $this->nombre = $ss;
		    if (($ss = strtok(".")) !== false)
		    {
			$this->mayor = intval($ss);
			if (($ss = strtok("\n ")) !== false) $this->menor = intval($ss);
		    }
		}
	}
  
	// Return the platform detected
  	function get_client_platform()
  	{
  		return ($this->platform);
  	}
  
  	// Return the browser that we detected
  	function get_client_browser()
	{
  		return ($this->browser);
  	}
  
  	// Return the user agent string
  	function get_user_agent()
  	{
  		return $_SERVER['HTTP_USER_AGENT'];
  	}
  
}
?>
