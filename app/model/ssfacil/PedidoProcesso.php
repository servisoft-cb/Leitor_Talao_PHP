<?php

class PedidoProcesso extends TSSFacilRecord
{
    const TABLENAME  = 'processo';
    const PRIMARYKEY = 'id';

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addattribute('usar_qtd_dobra');
        parent::addattribute('ler_talao');
    }
}