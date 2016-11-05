<?php

// Abre ou cria o arquivo bloco1.txt
// "a" representa que o arquivo é aberto para ser escrito
//@delete("../../public_key/".$_POST['nick'].".txt");
$fp = fopen("../pubkeys/".md5($_POST['nick']).".txt", "w");
 
// Escreve "exemplo de escrita" no bloco1.txt
$escreve = fwrite($fp, $_POST['public_key']);
 
// Fecha o arquivo
fclose($fp);
?>