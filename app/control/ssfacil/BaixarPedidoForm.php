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

        $label = new TLabel('Código de barras');
        if (self::isMobile())
        {
            $action = new TAction([$this, 'onExecute']);
            $url = $action->serialize(FALSE);
            $url = htmlspecialchars($url);
            $wait_message = AdiantiCoreTranslator::translate('Loading');
            
            $action = "Adianti.waitMessage = '$wait_message';";
            $action.= "__adianti_post_data('baixar_pedido_form', '{$url}');";
            $action.= "return false;";
            $codigo_barras = $this->makeTBarCodeInputReader(['name' => 'codigo_barras', 'maxlen' => 10, 'required' => true, 'label' => $label, 'onChangeFunction' => $action], 
                function($object){
                    $object->setExitAction(new TAction([$this, 'onCodBarExit'], ['static'=>'1']));
                }
            );
        } else 
        {
            $codigo_barras = $this->makeTEntry(['name' => 'codigo_barras', 'maxlen' => 10, 'label' => $label, 'required' => true], 
                function($object){
                    $object->setExitAction(new TAction([$this, 'onCodBarExit'], ['static'=>'1']));
                }
            );
        }

        $codigo_barras->addValidation('<b>Código de Barras</b>', new TMinLengthValidator, [10]);

        $this->form->addFields( [$label], [$codigo_barras] );

        $habLocalizacao = TSession::getValue('habLocalizacao');
        $label = new TLabel('Localização');
        $localizacao = $this->makeTEntry(['name' => 'localizacao', 'label' => $label, 'editable' => $habLocalizacao ?? false]);
        $this->form->addFields( [$label], [$localizacao] );

        if ($habLocalizacao)
            TUtils::setValidation($this->form, 'localizacao', [new TRequiredValidator]);

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

    public function onCodBarExit($param)
    {
        // $action = new TAction([$this, 'buscarCEP']);
        // $action->setParameters($param); 
        
        // if (!empty($param['cliea8cepres']))
        //     new TEdQuestion('Deseja buscar os dados deste CEP?', $action);

        try
        {
            TTransaction::open('ssfacil');
            
            // TUtils::setValidation($this->form, 'localizacao', [new TRequiredValidator]);

            if (empty($param['codigo_barras']))
                Exit;
            
            $infLocalizacao =InfLocalizacaoProc::execute($param['codigo_barras']);

            TTransaction::close();

            if ($infLocalizacao->r_informa == 'S')
            {
                TEntry::enableField('baixar_pedido_form', 'localizacao');
                TSession::setValue('habLocalizacao', true);
            } else 
            {
                TEntry::disableField('baixar_pedido_form', 'localizacao');
                TSession::delValue('habLocalizacao');
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

    public function onExecute($param)
    {
        try
        {
            TTransaction::open('ssfacil');

            $object = $this->form->getData();
            
            $this->form->validate();

            $baixaPedido = BaixaPedidoProc::execute($param['codigo_barras'], $param['localizacao']);

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