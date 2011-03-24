<?php

class mpago {
  var $_itens = array();
  var $_config = array ();
  var $_cliente = array ();
  /**
   * MercadoPago
   * Fun��o de inicializa��o
   * voc� pode passar os par�metros alterando as informa��es padr�o como o tipo de moeda e
   * os dados de configura��o da sua conta no MercadoPago
   
   */
  function mpago($args = array()) {
    if ('array'!=gettype($args)) $args=array();
    $default = array(
      'acc_id'=> '',
      'enc'=> '',
	  'url_process'=> '',
	  'url_succesfull'=> '',
      'currency'=> '',
	  'reseller_acc_id' => '',
	 
    );
    $this->_config = $args+$default;
  }
  /**
   
   * Retorna a mensagem de erro
   * @access public
   * @return string
   */
   
  function error($msg){
    trigger_error($msg);
    return $this;
  }
  /**
   * adicionar
   *
   * Adiciona um item ao carrinho de compras
   */
  function adicionar($item) {

    if ('array' !== gettype($item))
      return $this->error("Item precisa ser um array.");
    if(isset($item[0]) && 'array' === gettype($item[0])){
      foreach ($item as $elm) {
        if('array' === gettype($elm)) {
          $this->adicionar($elm);
        }
      }
      return $this;
    }

    $tipos=array(
      "id" =>         array(1,"string",                '@\w@'         ),
      "quantidade" => array(1,"string,integer",        '@^\d+$@'      ),
      "valor" =>      array(1,"double,string,integer", '@^\d*\.?\d+$@'),
      "descricao" =>  array(1,"string",                '@\w@'         ),
      "frete" =>      array(0,"string,integer",        '@^\d+$@'      ),
      "peso" =>       array(0,"string,integer",        '@^\d+$@'      ),
    );

  foreach($tipos as $elm=>$valor){
      list($obrigatorio,$validos,$regexp)=$valor;
      if(isset($item[$elm])){
        if(strpos($validos,gettype($item[$elm])) === false ||
          (gettype($item[$elm]) === "string" && !preg_match($regexp,$item[$elm]))){
          return $this->error("Valor invalido passado para $elm.");
        }
      }elseif($obrigatorio){
        return $this->error("O item adicionado precisa conter $elm");
      }
    }

    $this->_itens[] = $item;
    return $this;
  }
  /**
   * cliente
   *
   * Define o cliente a ser inserido no sistema.
   * Recebe como parametro um array associativo contendo os dados do cliente.
   *
     */
  function cliente($args=array()) {
    if ('array'!==gettype($args)) return;
    $this->_cliente = $args;
  }
  /**
   *
   * mostra
   *
   * Mostra o formul�rio de envio de post
   *
   * Configurar o objeto: voc� pode usar este m�todo para mostrar o
   * formul�rio com todos os inputs necess�rios para enviar.
   *
  
   */
  function mostra ($args=array()) {
    $default = array (
      'print'       => true,
      'open_form'   => true,
      'close_form'  => true,
      'show_submit' => true,
      'img_button'  => false,
      'bnt_submit'  => false,
    );
    $args = $args+$default;
    $_input = '  <input type="hidden" name="%s" value="%s"  />';
    $_form = array();
    if ($args['open_form'])
      $_form[] = '<form target="mercadopago" action="https://www.mercadopago.com/mlb/buybutton" method="post">';
    foreach ($this->_config as $key=>$value)
      $_form[] = sprintf ($_input, $key, $value);
    foreach ($this->_cliente as $key=>$value)
      $_form[] = sprintf ($_input, "$key", $value);

    $assoc = array (
      'id' => 'item_id',
      'descricao' => 'item_desc',
      'quantidade' => 'item_quant',
    );
    $i=1;
    foreach ($this->_itens as $item) {
      foreach ($assoc as $key => $value) {
        $sufixo=($this->_config['tipo']=="CBR")?'':'_'.$i;
        $_form[] = sprintf ($_input, $value.$sufixo, $item[$key]);
        unset($item[$key]);
      }
      $_form[] = str_replace ('.', '', sprintf ('  <input type="hidden" name="%s" value="%.2f"  />', "item_valor$sufixo", $item['valor']));
      unset($item['valor']);

      foreach ($item as $key=>$value)
        $_form[] = sprintf ($_input, "item_{$key}{$sufixo}", $value);

      $i++;
    }
    if ($args['show_submit']) {
      if ($args['img_button']) {
        $_form[] = sprintf('  <input type="image" src="%s" name="submit" alt="Mercado Pago"  />', $args['img_button']);
      } elseif ($args['btn_submit']) {
        switch ($args['btn_submit']) {
         
          default: $btn = 'buy_now_02_mlb.gif';
        }
        $_form[] = sprintf ('  <input type="image" src="https://www.mercadopago.com/org-img/MP3/buy_now_02_mlb.gif"  name="submit1" alt="Pagar" />', $btn);
      } else {
        $_form[] = '  <input type="submit" value="Pagar com o MercadoPago"  />';
      }
    }
    if($args['close_form']) $_form[] = '</form>';
    $return = implode("\n", $_form);
    if ($args['print']) print ($return);
    return $return;
  }
}

?>

