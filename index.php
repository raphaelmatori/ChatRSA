<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8' />
</head>

<script type="text/javascript">
	var serverIP = '10.255.5.160:5000'; //NECESSÁRIO CONFIGURAR
</script>

<style type="text/css">
		.main
		{
			top: 0;
			padding: 0;
		}
		.jumbotron
		{
			width: 400px;
			text-align: center;
			margin-right: auto;
			margin-left: auto;
			margin-top: 20px;
			padding: 20px;
		}
		.table
		{
			margin-top: 20px;
		}
		#myname
		{
			padding: 0;
			margin: 0;
		}

</style>
<?php 
$colours = array('007AFF','FF7000','FF7000','15E25F','CFC700','CFC700','CF1100','CF00BE','F00');
$user_colour = array_rand($colours);
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.min.css">
<script type="text/javascript" src="js/jsencrypt/bin/jsencrypt.js"></script>
<script type="text/javascript" src="./js/md5.min.js"></script>

<script language="javascript" type="text/javascript">  


var users_online = [];
var publicKey;
var privateKey;

var publicKey_c;
var privateKey_c;

var person = prompt("Please enter your name", "Guest"+ Math.floor(Math.random() * (99999 - 0 + 1)));
var lastPlainText = '';
var logged = [];

$(document).ready(function(){

	//create a new WebSocket object.
	var wsUri = "ws://"+serverIP+"/chat/server.php";
	websocket = new WebSocket(wsUri); 
	
	
	  var crypt = new JSEncrypt();
	  crypt.getKey();
      privateKey = crypt.getPrivateKey();
      publicKey= crypt.getPublicKey();

      crypt = new JSEncrypt();
      crypt.getKey();
      publicKey_c = crypt.getPrivateKey();
      privateKey_c = crypt.getPublicKey();

      jQuery.post( "./command/savePublicKey.php", { nick: person, public_key: publicKey, public_certificate: publicKey_c } ).done(function() {});
      $("#myname").append(""+person);  
      

        websocket.onopen = function(ev) {
        //$('#message_box').append("<div class=\"system_msg\">Connected!</div>");
        //send username to server
        var msg = {
            newuser: person 
        };
        websocket.send(JSON.stringify(msg));
   	 }

   	$('#private').append('<option selected value>=== selecione para PM ===</option>').val();
	$('#send-btn').click(function(){ //use clicks message send button	
		var mymessage = $('#message').val(); //get message text
		var myname = person;//$('#name').val(); //get user name
		var privateMsg = $('#private').val();
		lastPlainText = mymessage;

		if(myname == ""){ //empty name?
			alert("Enter your Name please!");
			return;
		}
		if(mymessage == ""){ //emtpy message?
			alert("Enter Some message Please!");
			return;
		}
		

		//Se a mensagem é privada, resgata a chave pública do destinatário
		if(privateMsg != '')
		{
			
			var publicKeyDst = "";
			
			$.ajax({
				url : "./pubkeys/"+md5(privateMsg)+".txt",
				async: false, 
			    dataType: "text",
			    success : function (data) {
			    publicKeyDst = data;
			    }
			});


			var digest = md5(mymessage);
			var signature = "";
			if(publicKeyDst != "")
			{
				crypt = new JSEncrypt();
				crypt.setPublicKey(publicKeyDst);//Utilizando a chave Pública do destinatário para encriptar
				
				mymessage = crypt.encrypt(mymessage);
				
			}
				crypt = new JSEncrypt();
				crypt.setPublicKey(privateKey_c);//assinando com minha chave privada o certificado
				signature = crypt.encrypt(digest);
				
			
		}

		var msg = {
		message: mymessage, //criptografar
		msgsignature: signature, //assinar
		name: myname,
		destination: privateMsg,
		color : '<?php echo $colours[$user_colour]; ?>'
		};

		//convert and send data to server
		websocket.send(JSON.stringify(msg));

	});
	


		websocket.onopen = function(ev) {
	        var msg = {
	            newuser: person 
	        };
	        	setInterval(function(){ 
	        websocket.send(JSON.stringify(msg));
	        }, 2000); 

			setInterval(function(){ 
	        	removeSeOffline();
	        }, 3000); 
	        

	   	 }
	 



	//#### Message received from server?
	websocket.onmessage = function(ev) {
		var msg = JSON.parse(ev.data); //PHP sends Json data
		var type = msg.type; //message type
		var umsg = msg.message; //message text
		var signature = msg.messagesignature; //hash
		var uname = msg.name; //user name
		var duname = msg.dname; //destination name
		var ucolor = msg.color; //color

		//enviando nome para atualizar no select
		rotinaNome(uname);
		//===================================================


		var backText = umsg;

		//Desencriptando a mensagem
		if(uname != "" && uname != undefined)
		{
			
			if(uname != person)
			{
				crypt = new JSEncrypt();
				crypt.setPrivateKey(privateKey);//Utilizando minha chave privada para ler
				umsg = crypt.decrypt(umsg);

			}
			else if(uname == person && duname == "")
			{
				crypt = new JSEncrypt();
				crypt.setPrivateKey(publicKey);//Utilizando minha chave publica para ler
				umsg = crypt.decrypt(umsg);

			}

			
			if(!umsg)
				umsg = backText;


			//confere a autenticidade
			if(duname == person)
			{
				var publicKeyDst = "";
			
				$.ajax({
					url : "./pubkeys/"+md5(uname)+"_c.txt",
					async: false, 
				    dataType: "text",
				    success : function (data) {
				    publicKeyDst = data;
				    }
				});


				crypt = new JSEncrypt();
				crypt.setPrivateKey(publicKeyDst);//Utilizando chave publica para ler
				signature = crypt.decrypt(signature);

				if(md5(umsg) != signature)
					{
						alert("MENSAGEM FALSA ->"+umsg);
					}
			}
		}


		//fim
	

		if(backText != null)
		{
			if(type == 'usermsg') 
			{
				if(uname != null && uname != person && (duname == person || duname == ''))
				{
					$('#message_box').prepend("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\"><br/><textarea cols='80' class='form-control'>"+umsg+"</textarea><br/></span></div>");
					//console.log("if1");
				}
				else if(uname == person && duname == null)
				{
					$('#message_box').prepend("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\"><br/><textarea cols='80' class='form-control'>"+backText+"</textarea><br/></span></div>");
					//console.log("if2");
				}
				else if(uname == person)
				{
					$('#message_box').prepend("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\"><br/><textarea cols='80' class='form-control'>"+lastPlainText+"</textarea><br/></span></div>");
					//console.log("if3");
				}
				
			}
			if(type == 'system')
			{
				$('#message_box').append("<div class=\"system_msg\">"+umsg+"</div>");
			}

			if(type != 'newUser')
				$('#message').val(''); //reset text
		}
	};
	
	websocket.onerror	= function(ev){$('#message_box').append("<div class=\"system_error\">Error Occurred - "+ev.data+"</div>");}; 
	websocket.onclose 	= function(ev){$('#message_box').append("<div class=\"system_msg\">Connection Closed</div>");}; 

	function rotinaNome(uname)
	{
		keepLogged(uname);

		if(uname != null && uname != this.person)
			this.users_online.push(uname);


		//=====================================CRIANDO O SELECT ========================
		var uniqueNames = [];
		$.each(this.users_online, function(i, el){
		    if($.inArray(el, uniqueNames) === -1) if(el != this.person) 
		    		uniqueNames.push(el);
		});

		this.users_online = uniqueNames;



		var clone = this.users_online.slice(0);
		clone.sort();



		var usrLogados = []
		for(var i = 0; i < this.logged.length; i++)
			for(var j = 0; j < this.users_online.length; j++)
				if(this.logged[i].name == this.users_online[j])
				{
					//console.log("vou manter "+this.logged[i].name);
					usrLogados.push(this.logged[i].name);
				}

		this.users_online = usrLogados;
		this.users_online.sort();

		var different = 0;
		if(this.users_online.length != clone.length)
		{
			for(var i = clone.length-1; i >=0; i--)
				if(clone[i] != this.users_online[i])
					different = 1;
		}

		for(var i = this.users_online-1; i >= 0; i--)
			if(this.users_online[i] != clone[i])
				different = 2;


		
			for(var i = 0; i < this.users_online.length; i++)
			{
				var encontrou = 0;
				var options = $('#private').children();
			
				for(var j = 0; j < options.length; j++)
					if(this.users_online[i] == options[j].innerHTML)
						encontrou = 1;

				if(encontrou != 1)
					different = 3;
			}
		  		
	
		if(different != 0 )
		{
			$('#private').find('option').remove().end();
			for(i = 0; i < this.users_online.length; i++)
				$('#private').append('<option value="'+this.users_online[i]+'">'+this.users_online[i]+'</option>').val(this.users_online[i]);
			$('#private').append('<option selected value="">=== selecione para PM ===</option>').val();
		}
	}


function keepLogged(name)
{
	if(name != null)
	{
		var  flag = 0;

		for(var i=0; i < this.logged.length; i++)
			if(this.logged[i].name == name)
			{
				flag = 1;
				this.logged[i].keep = 1;	
			}

		if(flag == 0)
			this.logged.push({'name':name,'keep':1});
	}
	
}

function perguntaSeOnline()
{
	for(var i=0; i < this.logged.length; i++)
	{
		this.logged[i].keep = 0;	
	}
}


function removeSeOffline()
{

	var auxIndex = [];

	for(var i=0; i < this.logged.length; i++)
		if(this.logged[i].keep == 0)
		{
			auxIndex.push(i);
		}
	
	for(var i=0; i < this.users_online.length; i++)
		for(var j=0; j < auxIndex.length; j++)
			if(this.logged[auxIndex[j]] != null)
			if(this.users_online[i] == this.logged[auxIndex[j]].name)
			{
				this.users_online.splice(i,1);
				this.logged.splice(j,1);
			}
			
	perguntaSeOnline();

}

});
</script>
<body>	

<div class="main">
	<div class="jumbotron">
		<h3 id="myname"></h3>
		<textarea class="form-control btn-block" name="message" id="message" placeholder="Mensagem" rows="10" cols="50"></textarea>
		<br>
			<div class="btn-group"><button class="btn btn-primary" id="send-btn" autofocus>Enviar</button>
			<div class="btn-group"><select id="private" class="form-control">	
			</select></div>
		</div>
		
		<div class="message_box btn-block"  id="message_box" onkeydown = "if (event.keyCode == 13)document.getElementById('send-btn').click();"></div>
	</div>
</div>

</body>
</html>
