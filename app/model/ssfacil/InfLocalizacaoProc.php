<?php

use Adianti\Registry\TSession;

class InfLocalizacaoProc extends TSSFacilRecord
{
    const TABLENAME  = 'prc_inf_localizacao';
    const PRIMARYKEY = '1';

    private $itens_processos;

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('r_informa');
        parent::addAttribute('r_total_itens');
        parent::addAttribute('r_total_produzido');
        parent::addAttribute('r_localizacao');
    }

    public static function execute($codigo_barras)
    {
        if ($conn = TTransaction::get())
        {
            $numero_pedido = substr($codigo_barras, 1, 6);
            $numero_item   = substr($codigo_barras, 7, 3);
            $sql = "execute procedure prc_inf_localizacao({$numero_pedido}, {$numero_item})";

            TTransaction::log($sql);
            
            $dbinfo = TTransaction::getDatabaseInfo(); // get dbinfo
            $result = $conn-> prepare ( $sql , array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $result-> execute ();
            
            if ($result)
            {
                $class = 'InfLocalizacaoProc';
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
}