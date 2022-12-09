<?php

use Adianti\Registry\TSession;

class BaixaPedidoProc extends TSSFacilRecord
{
    const TABLENAME  = 'prc_baixa_pedido_proc';
    const PRIMARYKEY = '1';

    private $itens_processos;

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('r_nome_processo');
        parent::addattribute('r_conferido');
        parent::addattribute('r_referencia');
        parent::addattribute('r_comprimento');
        parent::addattribute('r_largura');
        parent::addattribute('r_espessura');
        parent::addattribute('r_qtd_pecas');
        parent::addattribute('r_msg');
        parent::addattribute('r_transportadora');
    }

    public static function execute($codigo_barras, $localizacao)
    {
        if ($conn = TTransaction::get())
        {
            $numero_pedido = substr($codigo_barras, 1, 6);
            $numero_item   = substr($codigo_barras, 7, 3);
            $funcionario   = TSession::getValue('userid');
            if ($localizacao)
                $sLocalizacao   = "'" . $localizacao . "'";
            else
                $sLocalizacao   = 'null';
            $sql = "execute procedure prc_baixa_pedido_proc({$numero_pedido}, {$numero_item}, {$funcionario}, {$sLocalizacao})";

            TTransaction::log($sql);
            
            $dbinfo = TTransaction::getDatabaseInfo(); // get dbinfo
            $result = $conn-> prepare ( $sql , array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $result-> execute ();
            
            if ($result)
            {
                $class = 'BaixaPedidoProc';
                $fetched_object = $result-> fetchObject();
                if ($fetched_object)
                {
                    $object = new $class;
                    $object->fromArray( (array) $fetched_object );
                    $object->numero_pedido = intval($numero_pedido);
                    $object->numero_item   = intval($numero_item);
                }
                else
                {
                    $object = NULL; 
                }
                
            }
            
            return $object;
        }
        else
        {
            // if there's no active transaction opened
            throw new Exception('Pedido não encontrado');
        }
    }

    public function get_itens_processos()
    {
        TTransaction::open('ssfacil');
        if (empty($this->itens_processos))
        {
            $this->itens_processos = PedidoItemProcesso::where('id', '=', "(SELECT ID FROM PEDIDO WHERE NUM_PEDIDO = {$this->numero_pedido})")
                ->where('item', '=', $this->numero_item)
                ->orderBy('item_processo')
                ->load();
        }
        TTransaction::close('ssfacil');

        return $this->itens_processos;
    }
}