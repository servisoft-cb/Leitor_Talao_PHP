<?php

class PedidoItemProcesso extends TSSFacilRecord
{
    const TABLENAME  = 'pedido_item_processo';
    const PRIMARYKEY = 'id';

    use SystemChangeLogTrait;
    
    private $processo;

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('dtentrada');
        parent::addattribute('hrentrada');
        parent::addattribute('dtbaixa');
        parent::addattribute('hrsaida');
        parent::addattribute('qtd_dobra');
        parent::addattribute('id_processo');
    }

    public function get_ler_talao()
    {
        if (empty($this->processo)) {
            $this->processo = PedidoProcesso::findInTransaction('ssfacil', $this->id_processo);
        }
        return $this->processo->ler_talao;
    }

    public function get_nome_processo()
    {
        if (empty($this->processo)) {
            $this->processo = PedidoProcesso::findInTransaction('ssfacil', $this->id_processo);
        }

        if ($this->processo->usar_qtd_dobra === 'S')
        {
            return "{$this->processo->nome} QTD: {$this->qtd_dobra}";
        } else
            return $this->processo->nome;
    }
}