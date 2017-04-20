<?php
require_once "ltable_olib.php";

function mysql_41_password($in)
{
	$p=sha1($in,true);
	$p=sha1($p);
	return "*".strtoupper($p);
}

function ssha_encode($text)
{
	$salt = "";
	for ($i=1;$i<=10;$i++) $salt .= substr('0123456789abcdef',rand(0,15),1);
	$hash = "{SSHA}".base64_encode(pack("H*",sha1($text.$salt)).$salt);
	return $hash;
}
 
function ssha_check($text,$hash)
{
	$ohash = base64_decode(substr($hash,6));
	$osalt = substr($ohash,20);
	$ohash = substr($ohash,0,20);
	$nhash = pack("H*",sha1($text.$osalt));
	return $ohash == $nhash;
}

class mprs_login
{
	public $r, $user="", $pass="", $ipaddr="", $junky = '';
	
	public function set_password(lt_form $fo, $new_pass)
	{
		$isok = false;
		if ($this->r->authtype_id == 1)
		{
			$qb = new myquery($fo, sprintf("UPDATE usuarios SET passwd='%s' WHERE uid=%d", 
				mysql_41_password($new_pass), $this->r->uid), "SETPWD-1", true, true);
			$isok = $qb->isok;
		}
		if ($this->r->authtype_id == 2)
		{
			if (($ds = ldap_connect($this->r->ldap_host)) !== false)
			{
				if (ldap_bind($ds, $this->r->ldap_dn, $this->pass))
				{
					$entry['userPassword'] =  ssha_encode($new_pass);
					if (ldap_modify($ds, $this->r->ldap_dn, $entry))
					{ 
						$raa = array();
						$rv = -1;
						$cmd = sprintf("sudo /usr/bin/pws.sh %s %s 2>&1", 
							$new_pass, $this->user);
						//error_log($cmd);
						exec($cmd, $raa, $rv);
						//error_log(implode("\n", $raa));
						$isok = true;
					}
					else $fo->err("SETPWD-4", ldap_error($ds));
				}
				else $fo->err("SETPWD-2", "No pude enlazarme al servidor");
				ldap_close($ds);
			}
			else $fo->err("SETPWD-3", "No pude conectarme al servidor");
		}
		return $isok;
	}
	
	function check_ldap(lt_form $fo)
	{
		$isok = false;

		if (($ds = ldap_connect($this->r->ldap_host)) !== false)
		{
			if (ldap_bind($ds, $this->r->ldap_dn, $this->pass))
			{
				$isok = true;
			}
			else $fo->err("LOGIN-LDAP-2", "No pude enlazarme al servidor");
			ldap_close($ds);
		}
		else $fo->err("LOGIN-LDAP-1", "No pude conectarme al servidor");

		return $isok;
	}

	public function dup_check(lt_form $fo)
	{
		$dupok = true;
		$qb = new myquery($fo, sprintf("SELECT es.uid, ipaddr, name, allow_multiple FROM %s AS es " .
			"LEFT JOIN usuarios ON usuarios.uid=es.uid WHERE es.uid=%d AND abierta=1 AND es.ipaddr!='%s'", 
			$this->r->session_table, $this->r->uid, $this->ipaddr), "LOGIN-3", false);
		if ($qb->sz > 0)
		{
			$fo->warn("Sesion(es) abierta(s) en otra computadora", TRUE);
			foreach ($qb->a as $it)
			{
				$fo->warn(sprintf("Usuario: <b>%s</b> IP: <b>%s</b><br>", $it->name, $it->ipaddr), TRUE);
			}
			if ($qb->r->allow_multiple) $dupok = TRUE;
			else
			{
				$qc = new myquery($fo, sprintf("DELETE FROM %s WHERE uid=%d", $this->r->session_table, $this->r->uid), 
					"LOGIN-5", true, true);
				$dupok = $qc->isok;
			}
		}
		return $dupok;
	}
	
	public function __construct($user, $pass)
	{
		$this->user = $user;
		$this->pass = $pass;
		$this->ipaddr = get_ip_address();
	}
	
	public function auth_check(lt_form $fo)
	{
		$authok = false;
		$qa = new myquery($fo, sprintf("SELECT uid, usertype_id AS tipo, status, session_table, ".
			"authtype_id, ldap_host, ldap_dn, name, passwd ".
			"FROM usuarios WHERE UPPER(name)=UPPER('%s')", $this->user), "LOGIN-1");
		if ($qa->isok)
		{
            $u = &$qa->r;
			$this->r = new stdClass();
            $this->r->uid = $u->uid;
            $this->r->tipo = $u->tipo;
            $this->r->status = $u->status;
            $this->r->session_table = $u->session_table;
            $this->r->authtype_id = $u->authtype_id;
            $this->r->ldap_host = $u->ldap_host;
            $this->r->ldap_dn = $u->ldap_dn;
            $this->r->name = $u->name;
            $this->r->passwd = $u->passwd;

			if ($this->r->status == 1)
			{
				//error_log('AuthType: '.$this->r->authtype_id.' Hash: ['.mysql_41_password($this->pass).'] <=> ['.$this->r->passwd.']');
				if ($this->r->authtype_id == 1) $authok = (mysql_41_password($this->pass) == $this->r->passwd);
				if ($this->r->authtype_id == 2) $authok = $this->check_ldap($fo);
				//error_log($authok ? 1:0);
				if (!$authok) $fo->err("LOGIN-22", "Usuario o contrase&ntilde;a incorrecta");
			}
			else $fo->err("LOGIN-21", "Usuario inactivo");
		}
		else $fo->err("LOGIN-20", "Usuario o contrase&ntilde;a incorrecta");		
		return $authok;
	}
}

function login_check(lt_form $fo, $user, $pass)
{
	$isok = false;

	// TODO: create/check kerberos ticket
	
	$lg = new mprs_login($user, $pass);
	if ($lg->auth_check($fo))
	{
		//error_log('Auth check OK');
        $fo->uid = $lg->r->uid;
		if ($lg->dup_check($fo))
		{
			//error_log('Dup check OK');
			$r = &$lg->r;
			///$junky = session_id();
            $junky = sprintf("%07d", mt_rand(1,9999999));
			$qe = new myquery($fo, sprintf(
				"SELECT a.proyecto_id, b.nombre " .
				"FROM prjusr a ".
				"LEFT JOIN proyectos b ON b.proyecto_id=a.proyecto_id " .
				"WHERE a.uid=%d", $r->uid), "LOGIN-6");
			if ($qe->isok)
			{
				//error_log('Prjusr check OK');
				$qd = new myquery($fo, sprintf("REPLACE INTO %s VALUES (%d,'%s',NOW(),'','%s',1)",
					$r->session_table, $r->uid, $junky, $lg->ipaddr), "LOGIN-7", true, true);
				if ($qd->isok)
				{
					//error_log('Session update OK');

                    $expira = time() + 2592000; // 30d
                    setcookie('junk', $junky, $expira, '/');
					setcookie('uid', $r->uid, 0, '/');
					$_SESSION['uid'] = $r->uid;
					$_SESSION['unm'] = $user;
					$_SESSION['utp'] = $r->tipo;
					$_SESSION['junk'] = $junky;
					$_SESSION['session_tbl'] = $r->session_table;
					//error_log('uid:'.$_SESSION['uid']. ' pid:'.$_SESSION['']);
					
					proyecto_choose($fo, $qe->r->proyecto_id, $r->uid);
					
					$tema = 'default';
					$qt = new myquery($fo, sprintf("SELECT tema FROM ltable_themes WHERE uid=%d", $r->uid), 
						"LOGIN-8");
					if ($qt->isok) $tema = $qt->r->tema;
					$_SESSION['tema'] = $tema;
					
					if (strpos('luis.romero,miguel.romero', $user) === false)
					{
						$qn = new myquery($fo, sprintf("SELECT ipaddr FROM maquinas WHERE nombre='%s'", $user),
								"LOGIN-10");
						if ($qn->isok)
						{
							$qm = new myquery($fo, sprintf("UPDATE maquinas SET ipaddr='%s',actualizado=NOW() ".
									"WHERE nombre='%s'", $lg->ipaddr, $user), "LOGIN-9", false, true);
						}
						else
						{
							$qm = new myquery($fo, sprintf("INSERT INTO maquinas (nombre,ipaddr,actualizado,passwd,maquina_id) VALUES ".
									"('%s','%s',NOW(),'lrp16114',0)",
									$user, $lg->ipaddr), "LOGIN-11", true, true);
						}
					}
					$isok = true;
				}
			}
		}
	}
		
	return $isok;
}

function usuario_delcookies()
{
	setcookie('uid', '', time()-1, '/');
	setcookie('junk', '', time()-1, '/');
	setcookie('unm', '', time()-1, '/');
	setcookie('utp', '', time()-1, '/');
	setcookie('pid', '', time()-1, '/');
	setcookie('pnm', '', time()-1, '/');
}

function sesion_cerrar()
{
	$_SESSION = array();
	if (isset($_COOKIE[session_name()]))
	{
		setcookie(session_name(), '', time()-1, '/');
	}
	session_destroy();
}

function login_logout(lt_form $fo)
{
	if ($fo->dbopen())
	{
        $junky = isset($_COOKIE['junk']) ? $_COOKIE['junk']: $_SESSION['junk'];
        $q = sprintf("UPDATE %s SET salida=NOW(),abierta=0 WHERE uid=%d AND junky='%s'",
            $_SESSION["session_tbl"], $fo->uid, $junky); // TODO: cookie junk
        //error_log($q);
        myquery::q($fo, $q, "ES-CERRAR", FALSE, TRUE);
	}
	usuario_delcookies();
	sesion_cerrar();
}
?>