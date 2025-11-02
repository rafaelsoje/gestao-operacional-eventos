<?php

namespace App\Controllers;

use App\Models\Logs;
use App\Models\Operacional;
use App\Models\Propostas;
use App\Models\Users;
use CodeIgniter\Database\Query;
use PhpParser\Node\Scalar\Float_;
use stdClass;

class Home extends BaseController
{
    public function index()
    {
        $anoAtual = date('Y');
        $anoPassado = date('Y')-1;


        $items = new Propostas();

        $data = $items->select("YEAR(start_date) AS ano, QUARTER(start_date) AS trimestre, SUM(value) AS valor")
                ->where("YEAR(start_date) = $anoAtual OR YEAR(start_date) = $anoPassado")
                ->groupBy("ano, trimestre")
                ->orderBy("ano, trimestre")->findAll();

        $data = $items->select("YEAR(start_date) AS ano, QUARTER(start_date) AS trimestre, COUNT(*) AS total")
            ->where("YEAR(start_date) = $anoAtual OR YEAR(start_date) = $anoPassado")
            ->groupBy("ano, trimestre")
            ->orderBy("ano, trimestre")->findAll();

        // Inicializando os arrays para armazenar os totais
        $totaisPassado = [0, 0, 0, 0]; // Total de propostas por trimestre do ano passado
        $totaisAtual = [0, 0, 0, 0];   // Total de propostas por trimestre do ano atual

        // Processando os dados retornados da consulta
        foreach ($data as $row) {
            $ano = $row->ano;
            $trimestre = $row->trimestre - 1; // Ajustando o índice para 0 (para corresponder ao array de 0 a 3)
            $total = $row->total;

            // Verificando em qual ano e trimestre os dados devem ser armazenados
            if ($ano == $anoPassado) {
                $totaisPassado[$trimestre] = intval($total);
            } elseif ($ano == $anoAtual) {
                $totaisAtual[$trimestre] = intval($total);
            }
        }

        $recent = $items->select()->where('YEAR(start_date) >=', date('Y'))->orderBy('created_at', 'desc')->limit(6)->findAll();

        // Dados do gráfico
        $columnChart = [
            ['Trimestre', "$anoPassado", "$anoAtual"],
            ['1º Trimestre', $totaisPassado[0], $totaisAtual[0]],
            ['2º Trimestre', $totaisPassado[1], $totaisAtual[1]],
            ['3º Trimestre', $totaisPassado[2], $totaisAtual[2]],
            ['4º Trimestre', $totaisPassado[3], $totaisAtual[3]]
        ];

        // Obtendo as somas das categorias
        $data = (new Propostas())
        ->where('status', '1')
        ->where('YEAR(start_date)', date('Y'))
        ->selectSum('value')
        ->selectSum('charge')
        ->selectSum('rent')
        ->selectSum('third')
        ->first();

        // 2. Calculando o total
        $total = $data->value + $data->charge + $data->rent + $data->third;

        // 3. Calculando a porcentagem de cada categoria
        // $percentages = [
        //     'total' => ($data->value / $total) * 100,
        //     'equipe' => ($data->charge / $total) * 100,
        //     'locação' => ($data->rent / $total) * 100,
        //     'terceiros' => ($data->third / $total) * 100,
        // ];

        $saldo = $data->value - $data->charge - $data->rent - $data->third;

        $pieChart = [
            ['Categoria', 'Valor'],
            ['Equipe', intval($data->charge)],
            ['Locação', intval($data->rent)],
            ['Terceiros', intval($data->third)],
            ['Saldo', intval($saldo)],
        ];

         // Obtendo todas as somas em uma única consulta
         $data = (new Propostas())->where('status', '1')->where('YEAR(start_date)', date('Y'))
         ->selectSum('value')
         ->selectSum('charge')
         ->selectSum('rent')
         ->selectSum('third')
         ->first();  // Retorna um único registro com as somas

        // Verificando se os valores foram retornados corretamente
        $aceita = [
            'value' => $data->value ?? 0,   // Caso o valor seja NULL, substituímos por 0
            'charge' => $data->charge ?? 0,
            'rent' => $data->rent ?? 0,
            'third' => $data->third ?? 0
        ];

         // Obtendo todas as somas em uma única consulta
         $data = (new Propostas())->where('status', '2')->where('YEAR(start_date) >=', date('Y'))->where('start_date >=', date('Y-m-d'))
         ->selectSum('value')
         ->first();  // Retorna um único registro com as somas

        // Verificando se os valores foram retornados corretamente
        $analise = [
            'value' => $data->value ?? 0,   // Caso o valor seja NULL, substituímos por 0
        ];

        // Passando os totais para a view
        return view('dashboard', [
            'aceita' => $aceita,
            'analise' =>$analise,
            'saldo' => $saldo,
            'columnChart' => json_encode($columnChart),
            'pieChart' => json_encode($pieChart),
            'recent' => $recent,
            'botanic' => true
        ]);

    }

    public function proposta(): string
    {
        $anoPassado = date('Y')-1;
        $propostas = (new Propostas())->select()->where('YEAR(start_date) >=', date('Y'))->findAll() ;

        return view('proposta', ['propostas' => $propostas, 'botanicProp' => true]);
    }

    public function cadastroProposta(): string
    {
        return view('cadastroProposta',['botanicCad' => true]);
    }

    public function cadastroPropostaStore()
    {

        $validate = $this->validate([
            'excolab_id' => 'required',
            'type' => 'required',
            'start_date' => 'required',
            'event_name' => 'required',
            'value' => 'required',
            'status' => 'required',
        ]);

        if (!$validate) {
            return redirect()->route('cadastro.proposta')->with('error', 'Preencha o fornulario corretamente!');
        }

        $data = $this->request->getPost();

        $path = FCPATH . 'uploads/';

        $directoryName = $data['start_date']; // ex: "2024-11-06"

        $fullPath = $path . $directoryName;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        // Verifique se o arquivo foi enviado
        $file = $this->request->getFile('file');

        $newName = null;

        if ($file->isValid() && !$file->hasMoved()) {
            // Define o diretório de destino
            // $newName = $file->getRandomName(); // Gera um nome aleatório para o arquivo
            $newName = $file->getClientName();

            // Remove espaços e substitui por underscore
            $newName = preg_replace('/\s+/', '_', $newName);

            // Remove caracteres especiais, mantendo apenas letras, números e underscores
            $newName = preg_replace('/[^\w\-\.]/', '', $newName);

            $file->move($fullPath, $newName);
        }

        // $temp = explode('.', $data['value']);
        // $value = implode('',$temp);

        // $temp = explode(',', $value);
        // $value = implode('.',$temp);

        $value = str_replace(['.', ','], '', $data['value']);
        $value = substr($value, 0, -2) . '.' . substr($value, -2);

        if (!empty($data['charge'])) {
            $charge = str_replace(['.', ','], '', $data['charge']);
            $charge = substr($charge, 0, -2) . '.' . substr($charge, -2);
        }

        if (!empty($data['rent'])) {
            $rent = str_replace(['.', ','], '', $data['rent']);
            $rent = substr($rent, 0, -2) . '.' . substr($rent, -2);
        }

        if (!empty($data['third'])) {
            $third = str_replace(['.', ','], '', $data['third']);
            $third = substr($third, 0, -2) . '.' . substr($third, -2);
        }

        if (!empty($data['saldo'])) {
            // $saldo = trim($data['saldo']);
            $saldo = str_replace(['R$', ' '], '', $data['saldo']);
            $saldo = str_replace(['.', ','], '', $saldo);
            $saldo = substr($saldo, 0, -2) . '.' . substr($saldo, -2);
            $saldo = mb_substr($saldo, 1);
        }


        $items = [
            'excolab_id' => $data['excolab_id'],
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'event_name' => $data['event_name'],
            'client_name' => $data['client_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'value' => $value,
            'charge' => $charge ?? 0,
            'rent' => $rent ?? 0,
            'third'=> $third ?? 0,
            'saldo'=> $saldo ?? 0,
            'status' => $data['status'],
            'description' => $data['description'],
            'file' => $newName
        ];

        $prop = new Propostas();
        $result = $prop->insert($items);

        $rider = $this->request->getFile('rider');

        $newName = null;

        if($rider){
            if ($rider->isValid() && !$rider->hasMoved()) {
                // Define o diretório de destino
                // $newName = $file->getRandomName(); // Gera um nome aleatório para o arquivo
                $newName = $rider->getClientName();

                // Remove espaços e substitui por underscore
                $newName = preg_replace('/\s+/', '_', $newName);

                // Remove caracteres especiais, mantendo apenas letras, números e underscores
                $newName = preg_replace('/[^\w\-\.]/', '', $newName);

                $rider->move($fullPath, $newName);

                $item = [
                    'rider' => $newName
                ];

                $prop->update($result, $item);
            }
        }


        if($result){

            $user = session()->get('user');
            $data = [
                'title' => 'CADASTROU PROPOSTA',
                'description' => "O usuário " . $user->name  . " cadastrou " . $data['event_name'] . " " .date('d-m-Y H:i:s')
            ];

            $log = (new Logs())->insert($data);
            $this->telegran($user->name . " cadastrou \n" . $items['event_name']);
            return redirect()->route('proposta')->with('success', "Proposta cadastrada com sucesso!");
        }

        return redirect()->route('proposta')->with('error', 'Não foi possível gravar os dados.');
    }

    public function UpdateProposta($id){

        if(is_int(intval($id))){

            if($proposta = (new Propostas())->select()->where('id', $id)->first()){

                return view('updateProposta',['proposta' => $proposta]);
            }

            return redirect('proposta')->with('error', 'Proposta não encontrada.');
        }

        return redirect('proposta')->with('error', 'Id da proposta invãlido.');

    }

    public function UpdatePropostaStore()
    {

        $validate = $this->validate([
            'id' => 'required',
            'excolab_id' => 'required',
            'type' => 'required',
            'start_date' => 'required',
            'event_name' => 'required',
            'value' => 'required',
            'status' => 'required',
        ]);

        if (!$validate) {
            return redirect()->route('proposta')->with('error', 'Preencha o fornulario corretamente!');
        }

        $data = $this->request->getPost();

        $path = FCPATH . 'uploads/';

        $directoryName = $data['start_date']; // ex: "2024-11-06"

        $fullPath = $path . $directoryName;

        $file = $this->request->getFile('file');
        $rider = $this->request->getFile('rider');


        $fileOld = $path . $data['start_date_old'] . "/" . $data['file_old'];
        $fileNew = $fullPath . "/" . $data['file_old'];

        if($file){

            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            // Verifique se o arquivo foi enviado

            $newName = null;

            if ($file->isValid() && !$file->hasMoved()) {
                // Define o diretório de destino
                // $newName = $file->getRandomName(); // Gera um nome aleatório para o arquivo
                $newName = $file->getClientName();

                // Remove espaços e substitui por underscore
                $newName = preg_replace('/\s+/', '_', $newName);

                // Remove caracteres especiais, mantendo apenas letras, números e underscores
                $newName = preg_replace('/[^\w\-\.]/', '', $newName);

                $file->move($fullPath, $newName);

                $item = [
                    'file' => $newName
                ];

                $prop = new Propostas();
                $temp = $prop->update($data['id'], $item);

                unlink($fileOld);

            }else{

                if($data['start_date'] != $data['start_date_old']){
                    rename($fileOld, $fileNew);
                }
            }


        }


        if($rider){

            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            // Verifique se o arquivo foi enviado

            $newName = null;

            if ($rider->isValid() && !$rider->hasMoved()) {
                // Define o diretório de destino
                // $newName = $file->getRandomName(); // Gera um nome aleatório para o arquivo
                $newName = $rider->getClientName();

                // Remove espaços e substitui por underscore
                $newName = preg_replace('/\s+/', '_', $newName);

                // Remove caracteres especiais, mantendo apenas letras, números e underscores
                $newName = preg_replace('/[^\w\-\.]/', '', $newName);

                $rider->move($fullPath, $newName);

                $item = [
                    'rider' => $newName
                ];

                $prop = new Propostas();
                $temp = $prop->update($data['id'], $item);

                // unlink($fileOld);

            }else{

                // if($data['start_date'] != $data['start_date_old']){
                //     rename($fileOld, $fileNew);
                // }
            }


        }

        if(isset($data['removeRider']) && $data['removeRider'] == "on"){
            $item = [
                'rider' => ''
            ];

            $prop = new Propostas();
            $temp = $prop->update($data['id'], $item);
        }



        // $temp = explode('.', $data['value']);
        // $value = implode('',$temp);

        // $temp = explode(',', $value);
        // $value = implode('.',$temp);

        $value = str_replace(['.', ','], '', $data['value']);
        $value = substr($value, 0, -2) . '.' . substr($value, -2);

        if (!empty($data['charge'])) {
            $charge = str_replace(['.', ','], '', $data['charge']);
            $charge = substr($charge, 0, -2) . '.' . substr($charge, -2);
        }

        if (!empty($data['rent'])) {
            $rent = str_replace(['.', ','], '', $data['rent']);
            $rent = substr($rent, 0, -2) . '.' . substr($rent, -2);
        }

        if (!empty($data['third'])) {
            $third = str_replace(['.', ','], '', $data['third']);
            $third = substr($third, 0, -2) . '.' . substr($third, -2);
        }

        if (!empty($data['saldo'])) {
            // $saldo = trim($data['saldo']);
            $saldo = str_replace(['R$', ' '], '', $data['saldo']);
            $saldo = str_replace(['.', ','], '', $saldo);
            $saldo = substr($saldo, 0, -2) . '.' . substr($saldo, -2);
            $saldo = mb_substr($saldo, 1);
        }

        $items = [
            'excolab_id' => $data['excolab_id'],
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'event_name' => $data['event_name'],
            'client_name' => $data['client_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'value' => $value,
            'charge' => $charge ?? 0,
            'rent' => $rent ?? 0,
            'third'=> $third ?? 0,
            'saldo'=> $saldo ?? 0,
            'status' => $data['status'],
            'description' => $data['description'],
        ];




        $prop = new Propostas();
        $result = $prop->update($data['id'], $items);

        if($result){

            $user = session()->get('user');
            $data = [
                'title' => 'ATUALIZOU PROPOSTA',
                'description' => "O usuário " . $user->name  . " atualizou proposta " . $data['event_name'] . " ". date('d-m-Y H:i:s')
            ];

            $log = (new Logs())->insert($data);
            $this->telegran($user->name . " atualizou a proposta \n" . $items["event_name"]);
            return redirect()->route('proposta')->with('success', "Proposta atualizada com sucesso!");
        }

        return redirect()->route('proposta')->with('error', 'Não foi possível gravar os dados.');

    }

    public function removeProposta()
    {
        $id = $this->request->getPost('id');
        $file = $this->request->getPost('file');
        $date = $this->request->getPost('date');

        $path = FCPATH . 'uploads/' . $date . "/" . $file;

        if(is_int(intval($id))){

            if($prop = (new Propostas())->where('id',$id)->first()){
                $user = session()->get('user');
                $data = [
                    'title' => 'REMOVEU PROPOSTA',
                    'description' => "O usuário " . $user->name  . " removeu " . $prop->event_name . " " . date('d-m-Y H:i:s')
                ];

                $log = (new Logs())->insert($data);
                $this->telegran($user->name . " removeu \n" . $prop->event_name);
            }

            if($user = (new Propostas())->delete($id)){
                if (file_exists($path) && $file != "") {
                    unlink($path);
                }

                return redirect('proposta')->with('success', 'Proposta removida com sucesso!');
            }

            return redirect('proposta')->with('error', 'Não foi possível remover proposta');
        }

        return redirect()->route('proposta')->with('error', 'Parametro incorreto.');
    }


    ##############################################
    #               Operacional                  #
    ###############################################

    public function operacional(){

        $get = '';

        # caso receba ?id=0 no GET direciona para lançamentos futuros
        if($this->request->getGet()){
            $get = $this->request->getGet('id');            
        }

        if($this->request->getPost()){

            $post = $this->request->getPost();

            $chart = new stdClass;
            $chart->total_operacional = '';
            $chart->diesel_total = '';
            $chart->cedae_total = '';
            $chart->light_total = '';
            $chart->total_consumo = '';

            $barEntretenimento = new stdClass;
            $barEntretenimento->total_operacional = '';
            $barEntretenimento->diesel_total = '';
            $barEntretenimento->cedae_total = '';
            $barEntretenimento->light_total = '';
            $barEntretenimento->total_consumo = '';

            $barSocial = new stdClass;
            $barSocial->total_operacional = '';
            $barSocial->diesel_total = '';
            $barSocial->cedae_total = '';
            $barSocial->light_total = '';
            $barSocial->total_consumo = '';

            $barCorporativo = new stdClass;
            $barCorporativo->total_operacional = '';
            $barCorporativo->diesel_total = '';
            $barCorporativo->cedae_total = '';
            $barCorporativo->light_total = '';
            $barCorporativo->total_consumo = '';

            if($post['data_inicial'] && $post['data_final'])
            {

                if(isset($post['social']) || isset($post['corporativo']) || isset($post['entretenimento']))
                {

                    if(isset($post['social'])){
                        $array['social'] = $post['social'];
                    }
                    if(isset($post['corporativo'])){
                        $array['corporativo'] = $post['corporativo'];
                    }
                    if(isset($post['entretenimento'])){
                        $array['entretenimento'] = $post['entretenimento'];
                    }

                    $data = (new operacional)
                        ->where('data >=', $post['data_inicial'])
                        ->where('data <=', $post['data_final'])
                        ->where('flag', 1)
                        ->whereIn('tipo', $array)
                        ->orderBy('data', 'desc')
                    ->findAll();

                    $query = new Operacional();
                    $sum = $query
                        ->where('data >=', $post['data_inicial'])
                        ->where('data <=', $post['data_final'])
                        ->where('flag', 1)
                        ->whereIn('tipo', $array)
                        ->selectSum('super_op')
                        ->selectSum('diesel_total')
                        ->selectSum('tempo_g1')
                        ->selectSum('tempo_g2')
                        ->selectSum('taxa_cozinha')
                        ->selectsum('valor_premontagem')
                        ->selectSum('diesel_litros')
                        ->selectSum('cedae_consumo')
                        ->selectSum('cedae_total')
                        ->selectSum('light_consumo')
                        ->selectSum('light_total')
                        ->selectSum('total_consumo')
                    ->first();

                    $chart = (new Operacional())
                        ->where('data >=', $post['data_inicial'])
                        ->where('data <=', $post['data_final'])
                        ->where('flag', 1)
                        ->whereIn('tipo', $array)
                        ->selectSum('total_operacional')
                        ->selectSum('diesel_total')
                        ->selectSum('cedae_total')
                        ->selectSum('light_total')
                    ->first();

                    if(isset($array['social']))
                    {
                        $barSocial = (new Operacional())
                            ->where('data >=', $post['data_inicial'])
                            ->where('data <=', $post['data_final'])
                            ->where('tipo', 'Social')
                            ->where('flag', 1)
                            ->selectSum('total_operacional')
                            ->selectSum('diesel_total')
                            ->selectSum('cedae_total')
                            ->selectSum('light_total')
                            ->selectSum('total_consumo')
                        ->first();
                    }

                    if(isset($array['corporativo']))
                    {
                        $barCorporativo = (new Operacional())
                            ->where('data >=', $post['data_inicial'])
                            ->where('data <=', $post['data_final'])
                            ->where('tipo', 'Corporativo')
                            ->where('flag', 1)
                            ->selectSum('total_operacional')
                            ->selectSum('diesel_total')
                            ->selectSum('cedae_total')
                            ->selectSum('light_total')
                            ->selectSum('total_consumo')
                        ->first();
                    }

                    if(isset($array['entretenimento']))
                    {
                        $barEntretenimento = (new Operacional())
                           ->where('data >=', $post['data_inicial'])
                            ->where('data <=', $post['data_final'])
                            ->where('tipo', 'Entretenimento')
                            ->where('flag', 1)
                            ->selectSum('total_operacional')
                            ->selectSum('diesel_total')
                            ->selectSum('cedae_total')
                            ->selectSum('light_total')
                            ->selectSum('total_consumo')
                        ->first();
                    }
                }else{
                    return redirect()->back()->with('error', 'Nenhum tipo de evento selecionado!');
                }
            }else{
                return redirect()->back()->with('error', 'Data da pesquisa incorreta');
            }
        }else if($get == 0){
           
            $data = (new operacional)->orderBy('data', 'desc')->where('MONTH(data)', (date('m')+1))->where('flag', 0)->limit(4)->findAll();

            $query = new Operacional();
            $sum = $query
                ->where("MONTH(data)", (date('m')+1))
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 0)
                ->selectSum('super_op')
                ->selectSum('diesel_total')
                ->selectSum('tempo_g1')
                ->selectSum('tempo_g2')
                ->selectsum('valor_premontagem')
                ->selectSum('diesel_litros')
                ->selectSum('cedae_consumo')
                ->selectSum('cedae_total')
                ->selectSum('light_consumo')
                ->selectSum('light_total')
                ->selectSum('total_consumo')
            ->first();

             $chart = (new Operacional())
                ->where("MONTH(data)", (date('m')+1))
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 0)
                ->selectSum('total_operacional')
                ->selectSum('diesel_total')
                ->selectSum('cedae_total')
                ->selectSum('light_total')
            ->first();

            $barSocial = (new Operacional())
                ->where("MONTH(data)", (date('m')+1))
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 0)
                ->where('tipo', 'Social')
                ->selectSum('total_operacional')
                ->selectSum('diesel_total')
                ->selectSum('cedae_total')
                ->selectSum('light_total')
                ->selectSum('total_consumo')
            ->first();

            $barCorporativo = (new Operacional())
                ->where("MONTH(data)", (date('m')+1))
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 0)
                ->where('tipo', 'Corporativo')
                ->selectSum('total_operacional')
                ->selectSum('diesel_total')
                ->selectSum('cedae_total')
                ->selectSum('light_total')
                ->selectSum('total_consumo')
            ->first();

            $barEntretenimento = (new Operacional())
                ->where("MONTH(data)", (date('m')+1))
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 0)
                ->where('tipo', 'Entretenimento')
                ->selectSum('total_operacional')
                ->selectSum('diesel_total')
                ->selectSum('cedae_total')
                ->selectSum('light_total')
                ->selectSum('total_consumo')
            ->first();

        }else{

            $data = (new operacional)->orderBy('data', 'desc')->where('flag', 1)->limit(4)->findAll();

            $mesSearch = date('m');             

            // while(!(new Operacional()->where('MONTH(data)', $mesSearch)->findAll()))
            // {
            //     $mesSearch = $mesSearch -1;
            // }

            $query = new Operacional();
            $sum = $query
                ->where("MONTH(data)", $mesSearch)
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 1)
                ->selectSum('super_op')
                ->selectSum('diesel_total')
                ->selectSum('tempo_g1')
                ->selectSum('tempo_g2')
                ->selectSum('taxa_cozinha')
                ->selectsum('valor_premontagem')
                ->selectSum('diesel_litros')
                ->selectSum('cedae_consumo')
                ->selectSum('cedae_total')
                ->selectSum('light_consumo')
                ->selectSum('light_total')
                ->selectSum('total_consumo')
            ->first();

             $chart = (new Operacional())
                ->where("MONTH(data)", $mesSearch)
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 1)
                ->selectSum('total_operacional')
                ->selectSum('diesel_total')
                ->selectSum('cedae_total')
                ->selectSum('light_total')
            ->first();

            $barSocial = (new Operacional())
                ->where("MONTH(data)", $mesSearch)
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 1)
                ->where('tipo', 'Social')
                ->selectSum('total_operacional')
                ->selectSum('diesel_total')
                ->selectSum('cedae_total')
                ->selectSum('light_total')
                ->selectSum('total_consumo')
            ->first();

            $barCorporativo = (new Operacional())
                ->where("MONTH(data)", $mesSearch)
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 1)
                ->where('tipo', 'Corporativo')
                ->selectSum('total_operacional')
                ->selectSum('diesel_total')
                ->selectSum('cedae_total')
                ->selectSum('light_total')
                ->selectSum('total_consumo')
            ->first();

            $barEntretenimento = (new Operacional())
                ->where("MONTH(data)", $mesSearch)
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 1)
                ->where('tipo', 'Entretenimento')
                ->selectSum('total_operacional')
                ->selectSum('diesel_total')
                ->selectSum('cedae_total')
                ->selectSum('light_total')
                ->selectSum('total_consumo')
            ->first();


        }

        $donutChart = [
            ['Categorria', 'valor'],
            ['Equipe Operacional', (float)$chart->total_operacional],
            ['Diesel', (float)$chart->diesel_total],
            ['Cedae', (float)$chart->cedae_total],
            ['light', (float)$chart->light_total]
        ];

        $bar = [
            ['Custo', 'Equipe Operacional','Gerador', 'Cedae', 'Light'],
            ['Entretenimento',
                (float)$barEntretenimento->total_operacional,
                (float)$barEntretenimento->diesel_total,
                (float)$barEntretenimento->cedae_total,
                (float)$barEntretenimento->light_total
            ],
            ['Social',
                (float)$barSocial->total_operacional,
                (float)$barSocial->diesel_total,
                (float)$barSocial->cedae_total,
                (float)$barSocial->light_total
            ],
            ['Corporativo',
                (float)$barCorporativo->total_operacional,
                (float)$barCorporativo->diesel_total,
                (float)$barCorporativo->cedae_total,
                (float)$barCorporativo->light_total
            ],
        ];

        $barchart = [
            ['tipo', 'valor Total'],
            ['Entretenimento', (float)$barEntretenimento->total_consumo],
            ['Social', (float)$barSocial->total_consumo],
            ['Corporativo', (float)$barCorporativo->total_consumo],
        ];


        for($i=0; $i<=12; $i++){
            $mes = $i+1;
            $lineMes[] = (new Operacional())
                ->where("MONTH(data)", $mes)
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 1)
                ->selectSum('total_consumo')
            ->first();
        }

        $line = [
            ['Janeiro', (float)$lineMes[0]->total_consumo],
            ['Fevereiro', (float)$lineMes[1]->total_consumo],
            ['Março', (float)$lineMes[2]->total_consumo],
            ['Abril', (float)$lineMes[3]->total_consumo],
            ['Maio', (float)$lineMes[4]->total_consumo],
            ['Junho', (float)$lineMes[5]->total_consumo],
            ['Julho', (float)$lineMes[6]->total_consumo],
            ['Agosto', (float)$lineMes[7]->total_consumo],
            ['Setembro', (float)$lineMes[8]->total_consumo],
            ['Outubro', (float)$lineMes[9]->total_consumo],
            ['Novembro', (float)$lineMes[10]->total_consumo],
            ['Dezembro', (float)$lineMes[11]->total_consumo],
        ];

         for($x=0; $x<=12; $x++){
            $meses = $x+1;
            $cozinhaMes[] = (new Operacional())
                ->where("MONTH(data)", $meses)
                ->where("YEAR(data)", date('Y'))
                ->where('flag', 1)
                ->selectSum('taxa_cozinha')
            ->first();
        }

        $cozinha = [
            ['Janeiro', (float)$cozinhaMes[0]->taxa_cozinha],
            ['Fevereiro', (float)$cozinhaMes[1]->taxa_cozinha],
            ['Março', (float)$cozinhaMes[2]->taxa_cozinha],
            ['Abril', (float)$cozinhaMes[3]->taxa_cozinha],
            ['Maio', (float)$cozinhaMes[4]->taxa_cozinha],
            ['Junho', (float)$cozinhaMes[5]->taxa_cozinha],
            ['Julho', (float)$cozinhaMes[6]->taxa_cozinha],
            ['Agosto', (float)$cozinhaMes[7]->taxa_cozinha],
            ['Setembro', (float)$cozinhaMes[8]->taxa_cozinha],
            ['Outubro', (float)$cozinhaMes[9]->taxa_cozinha],
            ['Novembro', (float)$cozinhaMes[10]->taxa_cozinha],
            ['Dezembro', (float)$cozinhaMes[11]->taxa_cozinha],
        ];

        if(isset($post)){
            return view('operacional', ['operacional' => true, 'sum' => $sum, 'data' => $data, 'donut' => json_encode($donutChart), 'bar' => json_encode($bar), 'barChart' => json_encode($barchart), 'line' => json_encode($line), 'cozinha' => json_encode($cozinha), 'search' => $post, 'tipo' => $array]);
        }else{
            if($get == 0){
                return view('operacional', ['planejado' => true, 'sum' => $sum, 'data' => $data, 'donut' => json_encode($donutChart), 'bar' => json_encode($bar), 'barChart' => json_encode($barchart), 'line' => json_encode($line), 'cozinha' => json_encode($cozinha), 'get' => $get, 'mes' => $this->mes(date('m')+1)]);    
            }            
            return view('operacional', ['operacional' => true, 'sum' => $sum, 'data' => $data, 'donut' => json_encode($donutChart), 'bar' => json_encode($bar), 'barChart' => json_encode($barchart), 'line' => json_encode($line), 'cozinha' => json_encode($cozinha), 'mes' => $this->mes($mesSearch)]);
        }
    }

    public function cadastroOperacional(){
        return view('cadastroOperacional',['cadastroOp' => true]);
    }    

    public function operacionalLista(){
        $data = (new Operacional())->where('YEAR(data)', date('Y'))->where('flag', 1)->findAll();

        return view('operacionalLista', ['data'=> $data, 'listaOp' => true]);
    }

    public function cadastroOperacionalStore(){        

        $post = $this->request->getPost();

        $valor_diesel = str_replace(',', '.', $post['valor_diesel']);

        $valor_cedae = str_replace(',', '.', $post['cedae_valor']);

        $valor_light = str_replace(',', '.', $post['light_valor']);

        $cache_premontagem = str_replace(',', '.', $post['cache_premontagem']);
        $valor_operacional = str_replace(',', '.', $post['valor_operacional']);

        $cedae_total = str_replace('.','', $post['cedae_total']);
        $cedae_total = str_replace(',','.', $cedae_total);
        $cedae_total = number_format((Float)$cedae_total, 2, '.','');

        $light_total = str_replace('.','', $post['light_total']);
        $light_total = str_replace(',','.', $light_total);
        $light_total = number_format((Float)$light_total, 2, '.','');

        $diesel_total = str_replace('.','', $post['diesel_total']);
        $diesel_total = str_replace(',','.', $diesel_total);
        $diesel_total = number_format((Float)$diesel_total, 2, '.','');

        $total_consumo = str_replace('.','', $post['total_consumo']);
        $total_consumo = str_replace(',','.', $total_consumo);
        $total_consumo = number_format((Float)$total_consumo, 2, '.','');

        $valor_premontagem = str_replace('.', '', $post['valor_premontagem']);
        $valor_premontagem = str_replace(',', '.', $valor_premontagem);
        $valor_premontagem = number_format((float)$valor_premontagem, 2, '.', '');

        $super_op = str_replace('.', '', $post['super_op']);
        $super_op = str_replace(',', '.', $super_op);
        $super_op = number_format((float)$super_op, 2, '.', '');

        $total_operacional = str_replace('.', '', $post['total_operacional']);
        $total_operacional = str_replace(',', '.', $total_operacional);
        $total_operacional = number_format((float)$total_operacional, 2, '.', '');

        $taxa_cozinha = str_replace('.', '', $post['taxa_cozinha']);
        $taxa_cozinha = str_replace(',', '.', $taxa_cozinha);
        $taxa_cozinha = number_format((float)$taxa_cozinha, 2, '.', '');

   
        $data = [
            'nome' => $post['nome'],
            'data' => $post['data'],
            'tipo' => $post['tipo'],
            'hora_inicio' => isset($post['hora_inicio']) ? $post['hora_inicio'].':00' : null,
            'hora_fim' => isset($post['hora_fim'])? $post['hora_fim'].':00' : null,
            'hora_extra' => intval(isset($post['hora_extra'])? $post['hora_extra'] : null),
            'temp_inicio' => isset($post['temp_inicio']) ? $post['temp_inicio'] : null,
            'temp_fim' => isset($post['temp_fim'])? $post['temp_fim'] : null,
            'qtd_fornecedor' =>  intval(isset($post['qtd_fornecedor'])? $post['qtd_fornecedor']: null),
            'monitor_sonoro' => isset($post['monitor_sonoro']) ? $post['monitor_sonoro'] : null,
            'liga_g1' => isset($post['liga_g1']) ? $post['liga_g1'] : null,
            'desliga_g1' => isset($post['desliga_g1']) ? $post['desliga_g1'] : null,
            'tempo_g1' => isset($post['tempo_g1']) ? $post['tempo_g1'] : null,
            'liga_g2' => isset($post['liga_g2']) ? $post['liga_g2'] : null,
            'desliga_g2' => isset($post['desliga_g2']) ? $post['desliga_g2'] : null,
            'tempo_g2' => isset($post['tempo_g2']) ? $post['tempo_g2'] : null,
            'premontagem' => intval($post['premontagem']),
            'cache_premontagem' => $cache_premontagem,
            'valor_premontagem' => $valor_premontagem,
            'operacional' => intval($post['operacional']),
            'valor_operacional' => $valor_operacional,
            'super_op'=> $super_op,
            'total_operacional' => $total_operacional,
            'seguranca' => intval(isset($post['seguranca']) ? $post['seguranca'] : null),
            'bombeiro' => intval(isset($post['bombeiro']) ? $post['bombeiro'] : null),
            'limpeza' => intval(isset($post['limpeza']) ? $post['limpeza'] : null),
            'taxa_cozinha' => $taxa_cozinha,
            'publico_final' => intval(isset($post['publico_final']) ? $post['publico_final'] : null),
            'publico_tribuna' => intval(isset($post['publico_tribuna'])? $post['publico_tribuna'] : null),
            'publico_saida' => intval(isset($post['publico_saida']) ? $post['publico_saida'] : null),
            'valor_diesel' => $valor_diesel,
            'diesel_inicio' => intval($post['diesel_inicio']),
            'diesel_fim' => intval($post['diesel_fim']),
            'diesel_litros' => intval($post['diesel_litros']),
            'diesel_total' => $diesel_total,
            'cedae_valor' => $valor_cedae,
            'cedae_inicio' => intval($post['cedae_inicio']),
            'cedae_fim' => intval($post['cedae_fim']),
            'cedae_consumo' => intval($post['cedae_consumo']),
            'cedae_total' => $cedae_total,
            'light_valor' => $valor_light,
            'light_inicio' => intval($post['light_inicio']),
            'light_fim' => intval($post['light_fim']),
            'light_consumo' => intval($post['light_consumo']),
            'light_total' => $light_total,
            'total_consumo' => $total_consumo,
            'descricao' => $post['descricao'],
            'flag' => intval($post['flag']),
        ];

        $user = session()->get('user');

        if(isset($post['id'])){

            if((new Operacional())->update($post['id'], $data)){

                $log = [
                    'title' => 'ATUALIZAÇÃO OPERACIONAL',
                    'description' => "$user->name atualizou um cadastro oparacional. " . $data['nome'] . " em " . date('d-m-Y H:i:s'),
                ];

                (new Logs())->insert($log);
                $this->telegran($user->name . " atualizou um cadatro \n" . $data['nome']);
                if($post['flag'] == 0){
                    return redirect()->route('planejamento.lista')->with('success', "Atualzaçao realizada com sucesso! teste");
                }

                return redirect()->route('operacional.lista')->with('success', "Atualzaçao realizada com sucesso! teste");
            }

        }else{
            
            if($result = (new Operacional())->insert($data)){

                $log = [
                    'title' => 'CADASTRO OPERACIONAL',
                    'description' => "$user->name realizou um novo cadastro oparacional. " . $data['nome'] . " em " . date('d-m-Y H:i:s'),
                ];

                (new Logs())->insert($log);
                $this->telegran($user->name . " novo cadastro operaconal \n" . $data['nome']);
                if($post['flag'] == 0){
                    return redirect()->route('planejamento.lista')->with('success', "Cadastro realizado com sucesso!");
                }

                return redirect()->route('operacional.lista')->with('success', "Cadastro realizado com sucesso!");
            }
        }

        return redirect()->route('operacional.lista')->with('error', "Erro ao fazer cadastro!");
    }

    public function updateOperacional($id)
    {
        if(is_int(intval($id)))
        {
            $temp = new Operacional();
            $item = $temp->select()->where('id', $id)->first();

            if($item->flag == 0){
                return view('cadastroPlanejamento', ['item' => $item]);   
            }

            return view('cadastroOperacional', ['item' => $item]);
        }

        return redirect()->route('operacional')->with('error', "Parametro incorreto");

    }

    public function deleteOperacional()
    {
        $data = $this->request->getPost();

        $user = session()->get('user');

        $log=[
            'title' => "INFORMAÇÂO DELETADA",
            'description' => "$user->name apagou um cadastro oparacional. " . $data['nome'] . " em " . date('d-m-Y H:i:s'),
        ];

        if($temp = (new Operacional())->delete($data['id'])){

            $info = new Logs();
            $info->insert($log);
            $this->telegran($user->name . " apagou \n" . $data['nome']);


            return redirect()->route('operacional.lista')->with('success', "Informação deletada com sucesso.");
        }

        return redirect()->route('operacional.lista')->with('error', "Informação não pode ser removida.");

    }

    public function cadastroPlanejamento()
    {
        return view('cadastroPlanejamento', ['planejado' => true]);
    }

    public function planejamentoLista()
    {
        $data = (new Operacional())->where('YEAR(data)', date('Y'))->where('flag', 0)->findAll();
        return view('operacionalLista', ['data' => $data, 'flag' => 0, 'planejado' => true]);
    }

    public function usuarios(): string
    {
        $list = new Users();
        $users = $list->select()->findAll();

        return view('usuarios', ['users' => $users, 'botanicUser' => true]);
    }

    public function cadastroUsuario(): string
    {
        return view('cadastroUsuario');
    }

    public function usuarioStore()
    {
        $validate = $this->validate([
            'name' => 'required',
            'email' => 'required|valid_email',
            'password' => 'required',
        ]);

        if (!$validate) {
            // return redirect()->route('login')->with('errors', $this->validator->getErrors());
            return redirect()->route('usuario')->with('error', 'Preencha o fornulario corretamente!');
        }

        $data = $this->request->getPost();
        $pass = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        $data['avatar'] = "avatar.svg";
        $data['password'] = $pass;

        $add = new Users();
        $result = $add->insert($data);

        if($result){
            $user = session()->get('user');
            $this->telegran($user->name . " cadastrou um usuario \n" . $data['name']);
            return redirect()->route('usuarios');
        }

        return redirect()->route('usuario')->with('error', 'Não foi possível cadastrar usuário.');

    }

    public function updateUsuario($id)
    {
        if(is_int(intval($id)))
        {
            $temp = new Users();
            $user = $temp->select()->where('id', $id)->first();
            unset($user->password);


            return view('updateUsuario', ['user' => $user]);
        }
    }

    public function updateUsuarioStore()
    {
        // if (!session()->has('auth')) {
        //     return redirect()->route('login')->with('error', 'Sessão expirada!!!');
        // }

        $validate = $this->validate([
            'id' => 'required',
            'name' => 'required',
            'admin' => 'required',
        ]);

        $data = $this->request->getPost();

        // $avatar = $this->validate(['avatar' => 'uploaded[image]|is_image[image]|ext_in[image,jpg,png,jpeg]']);
        $avatar = $this->validate([
            'avatar' => [
                'rules' => 'uploaded[avatar]|is_image[avatar]|ext_in[avatar,jpg,png,jpeg]',
                'errors' => [
                    'uploaded' => 'Você deve selecionar um arquivo para upload.',
                    'is_image' => 'O arquivo enviado não é uma imagem válida.',
                    'ext_in' => 'A imagem deve estar nos formatos: jpg, png, jpeg.',
                ],
            ],
        ]);

        if ($avatar) {

            $img = $this->request->getFile('avatar');

            //$name = $img->getRandomName();
            $name = sha1(uniqid()) . '.webp';

            $data['avatar'] = $name;

            \Config\Services::image('gd')
                ->withFile($img)
                ->convert(IMAGETYPE_WEBP)
                ->resize(200, 237, true, 'auto')
                ->save(FCPATH . 'media/' . $name);

            if (!$img->hasMoved()) {

                $avatar = [
                    'avatar' => $name
                ];

                $item = new Users();
                $user = $item->update($data['id'], $avatar);

            }

        } else{

            $errors = $this->validator->getErrors('avatar');
            return redirect()->route('usuarios')->with('error', $errors['avatar']);
        }

        if($data['password']){

            $item = [
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ];

            $pass = new Users();
            $result = $pass->update($data['id'], $item);
        }

        $items = [
            'name' => $data['name'],
            'admin' => $data['admin'],
        ];

        $temp = new Users();
        $update = $temp->update($data['id'], $items);

        if (!$update) {
            return redirect()->route('usuarios')->with('error', 'Erro ao Gravar dados.');
        }

        $instance  = new Users();
        $user = $instance->select()->where('id', $data['id'])->first();

        $userInfo = new stdClass;
        $userInfo->id = $user->id;
        $userInfo->name = $user->name;
        $userInfo->email = $user->email;
        $userInfo->admin = $user->admin;
        $userInfo->avatar = $user->avatar;

        session()->set('user', $userInfo);

        $user = session()->get('user');
        $data = [
            'title' => 'ATUALIZOU USUÁRIO',
            'description' => "O usuário " . $user->name  . " atualizou seu perfil em " . date('d-m-Y H:i:s')
        ];

        $log = (new Logs())->insert($data);

        return redirect()->route('usuarios')->with('success', 'Dados atualizados com sucesso!');
    }

    public function removeUsuario($id)
    {
        if(is_int(intval($id))){
            if($user = (new Users())->delete($id)){
                $user = session()->get('user');
                $this->telegran($user->name . " removeu usuario");
                return redirect('usuarios')->with('success', 'Usuário removido com sucesso!');
            }

            return redirect('usuarios')->with('error', 'Não foi possível remover usuário');
        }

        return redirect()->route('usuarios')->with('error', 'Parametro incorreto.');
    }

    public function calendario()
    {
        $model = new Propostas();

        $ano = $this->request->getGet('ano') ?? date('Y');        

        $eventos = $model->where('YEAR(start_date)', $ano)->findAll();

        $data = array_map(function ($e) {
            // cor por status
            switch ($e->status) {
                case '1': $cor = '#28a745'; break; // aceita
                case '2': $cor = '#e0a800'; break; // em análise (melhor que #ffc107)
                case '3': $cor = '#dc3545'; break; // rejeitada
                default:  $cor = '#3788d8';
            }

            return [
                'id'    => $e->id,
                'title' => $e->event_name,
                'start' => $e->start_date,
                'end'   => $e->end_date,
                'color' => $cor,
                // extendedProps:
                'status'    => $e->status,
                'type'      => $e->type ?? '',
                'descricao' => $e->description ?? '',
            ];
        }, $eventos);
       
        return view('calendario', [
            'ano'      => $ano,
            'fcEvents' => json_encode($data), // string JSON
            'calendario' => true,
        ]);
    }


    public function mes($num)
    {
        switch($num){
            case '01':
            case '1':
                return "Janeiro";
                break;
            case '02':
            case '2':
                return "Fevereiro";
                break;
            case '03':
            case '3':
                return "Março";
                break;
            case '04':
            case '4':
                return "Abril";
                break;
            case '05':
            case '5':
                return "Maio";
                break;
            case '06':
            case '6':    
                return "Junho";
                break;
            case '07':
            case '7':
                return "Julho";
                break;
            case '08':
            case '8':
                return "Agosto";
                break;
            case '09':
            case '9':
                return "Setembro";
                break;
            case '10':                
                return "Outubro";
                break;
            case '11':
                return "Novembro";
                break;
            case '12':
                return "Dezembro";
                break;
            default:
                return "Não encontrado";
                break;
        }
    }

    public function calendarioOperacional()
    {
        
        $model = new Operacional(); // ajuste o nome do Model correto

        $ano = $this->request->getGet('ano') ?? date('Y');        

        // busca pelo ano na coluna 'data'
        $eventos = $model->where('YEAR(data)', $ano)->findAll();

        $data = array_map(function ($e) {
            // cor pode variar pelo tipo ou flag, ajuste como preferir
            switch ($e->tipo) {
                case 'show':      $cor = '#28a745'; break;
                case 'evento':    $cor = '#e0a800'; break;
                case 'serviço':   $cor = '#dc3545'; break;
                default:          $cor = '#3788d8';
            }

            // monta start/end combinando data + hora
            $start = $e->data . ' ' . ($e->hora_inicio ?? '00:00:00');
            $end   = $e->data . ' ' . ($e->hora_fim ?? '23:59:59');

            return [
                'id'    => $e->id,
                'title' => $e->nome,
                'start' => $start,
                'end'   => $end,
                'allDay' => true,
                'color' => $cor,
                // extendedProps
                'tipo'          => $e->tipo,
                'descricao'     => $e->descricao ?? '',
                'hora_extra'    => $e->hora_extra,
                'qtd_fornecedor'=> $e->qtd_fornecedor,
                'flag'          => $e->flag,
                'consumos'      => [
                    'diesel' => [
                        'inicio'   => $e->diesel_inicio,
                        'fim'      => $e->diesel_fim,
                        'litros'   => $e->diesel_litros,
                        'total'    => $e->diesel_total,
                        'valor'    => $e->valor_diesel,
                    ],
                    'cedae' => [
                        'inicio'   => $e->cedae_inicio,
                        'fim'      => $e->cedae_fim,
                        'consumo'  => $e->cedae_consumo,
                        'total'    => $e->cedae_total,
                        'valor'    => $e->cedae_valor,
                    ],
                    'light' => [
                        'inicio'   => $e->light_inicio,
                        'fim'      => $e->light_fim,
                        'consumo'  => $e->light_consumo,
                        'total'    => $e->light_total,
                        'valor'    => $e->light_valor,
                    ],
                ],
                'custos' => [
                    'premontagem'      => $e->valor_premontagem,
                    'operacional'      => $e->valor_operacional,
                    'total_operacional'=> $e->total_operacional,
                    'seguranca'        => $e->seguranca,
                    'bombeiro'         => $e->bombeiro,
                    'limpeza'          => $e->limpeza,
                ],
                'publico' => [
                    'final'   => $e->publico_final,
                    'tribuna' => $e->publico_tribuna,
                    'saida'   => $e->publico_saida,
                ],
            ];
        }, $eventos);

        return view('calendarioOperacional', [
            'ano'        => $ano,
            'fcEvents'   => json_encode($data),
            'calendarioOp' => true,
        ]);

    }

    public function logs()
    {
        $data = (new Logs())->select()->orderBy('id', 'desc')->limit(50)->findAll();

        return view('logs', ['logs' => $data]);
    }

    public function telegran($texto)
    {
        // Telegram
        // Monta a mensagem para o Telegram
        // $texto = "📝 Novo Login efetuado:\n\n";
        // $texto .= "👤 Nome: " . htmlspecialchars($nome) . "\n";
        // $texto .= "📧 E-mail: " . htmlspecialchars($email) . "\n";
        // $texto .= "Assunto: " . htmlspecialchars($assunto) . "\n";
        // $texto .= "💬 Mensagem: " . htmlspecialchars($mensagem);

        // Dados do bot
        $bot_token = env('TELEGRAM_BOT_TOKEN'); 
        $chat_id = env('TELEGRAM_CHAT_ID');

        // Prepara a chamada cURL
        $url = "https://api.telegram.org/bot$bot_token/sendMessage";

        $dados = [
            'chat_id' => $chat_id,
            'text' => $texto,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dados));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $resposta = curl_exec($ch);
        $erro = curl_error($ch);
        curl_close($ch);

    }

}
