<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TButton;
use Adianti\Database\TTransaction;
use Adianti\Validator\TRequiredValidator;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Validator\TMinLengthValidator;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TBarCodeInputReader;

class BaixarPedidoForm extends TPage
{
    public $form;
    public $dataGrid;

    use UIBuilderTrait;
    
    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('baixar_pedido_form');
        $this->form->setFormTitle('Baixar Pedido');

        if (self::isMobile())
        {
            $action = new TAction([$this, 'onExecute']);
            $url = $action->serialize(FALSE);
            $url = htmlspecialchars($url);
            $wait_message = AdiantiCoreTranslator::translate('Loading');
            
            $action = "Adianti.waitMessage = '$wait_message';";
            $action.= "__adianti_post_data('baixar_pedido_form', '{$url}');";
            $action.= "return false;";
            $codigo_barras = $this->makeTBarCodeInputReader(['name' => 'codigo_barras', 'maxlen' => 10, 'required' => true, 'onChangeFunction' => $action]);
        } else 
        {
            $codigo_barras = $this->makeTEntry(['name' => 'codigo_barras', 'maxlen' => 10, 'required' => true]);
        }

        $codigo_barras->addValidation('<b>Código de Barras</b>', new TMinLengthValidator, [10]);

        $this->form->addFields( [new TLabel('Código de barras')], [$codigo_barras] );

        $entrada_transformer = function($value, $object, $row) {
            if ($value) 
            {
                $date = new DateTime($value);
                $row->style = "background:#FF8000;color:white";
                return $date->format('d/m/Y') . ' ' . $object->hrentrada;
            } else
            {
                return "";
            }
        };

        $saida_transformer = function($value, $object, $row) {
            if ($value) 
            {
                $date = new DateTime($value);
                return $date->format('d/m/Y') . ' ' . $object->hrsaida;
            } else
            {
                return "";
            }
        };

        $panel = $this->makeTDataGrid([
            'name' => 'datagrid',
            'pagenavigator' => false,
            'columns' => [
                ['name' => 'nome_processo', 'label' => 'Processo', 'width' => '64%', 'align' => 'left'  ],
                ['name' => 'dtentrada'    , 'label' => 'Entrada' , 'width' => '13%', 'align' => 'center', 'transformer' => $entrada_transformer],
                ['name' => 'dtbaixa'      , 'label' => 'Saída'   , 'width' => '13%', 'align' => 'center', 'transformer' => $saida_transformer],
                ['name' => 'ler_talao'    , 'label' => 'Ler'     , 'width' => '10%', 'align' => 'center']
            ],
        ]);
        $this->dataGrid = $this->getWidget('datagrid');

        $this->form->addAction('Executar', new TAction([$this, 'onExecute']));

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(TUtils::createXMLBreadCrumb('menu.xml', 'BaixarPedidoForm'));
        $container->add($this->form);
        $container->add($panel);

        // foreach(PDO::getAvailableDrivers() as $driver) {
        //     echo $driver . '<br>';
        //   }
        
        parent::add($container);
    }

    public function onExecute($param)
    {
        try
        {
            TTransaction::open('ssfacil');

            $object = $this->form->getData();
            
            $this->form->validate();

            $baixaPedido = BaixaPedidoProc::execute($param['codigo_barras']);

            TTransaction::close();

            if (empty($baixaPedido->r_msg))
            {
                $this->dataGrid->addItems($baixaPedido->itens_processos);

                $data = new stdClass;
                $data->codigo_barras = '';
                // TForm::sendData('baixar_pedido_form', $data, false, false, 500);
                TScript::create("limpar();");
            } else 
            {
                new TMessage('error', $baixaPedido->r_msg);
            }
        }
        catch (Exception $e) 
        {
            $object = $this->form->getData();
            $this->form->setData($object);

            new TMessage('error', $e->getMessage());
            
            TTransaction::rollback();
        }
    }
}