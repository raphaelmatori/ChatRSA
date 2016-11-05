<?php
include_once('phpseclib/Crypt/DES.php');
include_once('phpseclib/Crypt/TripleDES.php');
include_once('phpseclib/Crypt/AES.php');
include_once('phpseclib/Crypt/RC4.php');
include_once('phpseclib/Crypt/RSA.php');
include_once('phpseclib/Crypt/Hash.php');

if(isset($_POST['des_key']) && isset($_POST['private_key']) && isset($_POST['public_key1']) && isset($_POST['public_key2'])){
	$des_key = $_POST['des_key'];
	$private_key = $_POST['private_key'];
	$public_key1 = $_POST['public_key1'];
	$public_key2 = $_POST['public_key2'];	
	$msg = $_POST['msg'];
	
	if($_POST['op'] == 'cript'){		
		switch($_POST['modo']){
			case 'des': 
				$des = new Crypt_DES();
				$des->setKey($des_key);
				$result = $des->encrypt($msg);
			break;								
			case 'rsa1': 
				$rsa = new Crypt_RSA();	
				$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
				$rsa->loadKey($private_key);
				$result = base64_encode($rsa->encrypt($msg));
			break;			
			case 'rsa2':
				$rsa = new Crypt_RSA();	
				$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
				$rsa->loadKey($public_key1);
				$result = base64_encode($rsa->encrypt($msg));
			break;			
			case 'rsa3':
				$rsa = new Crypt_RSA();	
				$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
				$rsa->loadKey($public_key2);
				$result = base64_encode($rsa->encrypt($msg));
			break;	
			case 'md5':
				$md5 = new Crypt_Hash('md5');
				$result 	= $md5->hash($msg);			
			break;
			case 'sha1':
				$sha1 = new Crypt_Hash('sha1');
				$result = $sha1->hash($msg);			
			break;
		}		
	}else{		
		switch($_POST['modo']){
			case 'des': 
				$des = new Crypt_DES();
				$des->setKey($des_key);
				$result = $des->decrypt($msg);
			break;							
			case 'rsa1': 
				$rsa = new Crypt_RSA();	
				$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
				$rsa->loadKey($private_key);
				$result = $rsa->decrypt(base64_decode($msg));
			break;			
			case 'rsa2':
				$rsa = new Crypt_RSA();	
				$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
				$rsa->loadKey($public_key1);
				$result = $rsa->decrypt(base64_decode($msg));
			break;			
			case 'rsa3':
				$rsa = new Crypt_RSA();	
				$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
				$rsa->loadKey($public_key2);
				$result = $rsa->decrypt(base64_decode($msg));
			break;	
			case 'md5':
				$md5 = new Crypt_Hash('md5');
				$result 	= $md5->hash($msg);			
			break;
			case 'sha1':
				$sha1 = new Crypt_Hash('sha1');
				$result = $sha1->hash($msg);			
			break;			
		}		
	}		
}else{	
	$rsa = new Crypt_RSA();
	extract($rsa->createKey());		
	$des_key = 'abcdefgh';
	$private_key = $privatekey;
	$public_key1 = $publickey;
	$public_key2 = "";
	$msg = "";
	$result = "";
}
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>	
<form action="criptografia.php" method="POST">
<table style="width:100%">
<tr>
    <th>Chave Simetrica(DES)</th>
    <th>Sua Chave Privada (RSA)</th> 
    <th>Sua Chave Publica (RSA)</th>
	<th>Outra Chave Publica (RSA)</th>
 </tr>
 <tr>
    <td><input type="text" name="des_key"  value='<?php echo $des_key;?>' maxlength="8"/></td>
    <td><textarea name="private_key" cols="50" rows="10" ><?php echo $private_key;?></textarea></td> 
    <td><textarea name="public_key1" cols="50" rows="10" ><?php echo $public_key1;?></textarea></td>
	<td><textarea name="public_key2" cols="50" rows="10" ><?php echo $public_key2;?></textarea></td>
 </tr>
</table>

<h3> Operacao: </h3> 
<select name="op">
<option value="cript">Encriptar</option>
<option value="decript">Decriptar</option>
</select>

<select name="modo">
<option value="des">DES</option>
<option value="rsa1">RSA - Chave Privada</option>
<option value="rsa2">RSA - Chave Publica</option>
<option value="rsa3">RSA - Chave Publica - Outra</option>
<option value="md5">MD5</option>
<option value="sha1">SHA1</option>
</select>

<input type="submit" name="btn1" value="Executar">

<table style="width:100%">
<tr>
    <th>Mensagem</th>
    <th>Resultado</th> 
 </tr>
 <tr>
    <td><textarea name="msg" cols="80" rows="10"><?php echo $msg;?></textarea></td>
    <td><textarea name="result" cols="80" rows="10"><?php echo $result;?></textarea></td> 
 </tr>

</table>
</form>
</body>
</html>