<?php

use App\Models\Employee;
use App\Models\LegacyIndividual;
use App\Models\LegacyRace;
use App\Models\LegacyStudent;
use App\Services\FileService;
use App\Services\UrlPresigner;

return new class extends clsDetalhe {
    public function Gerar()
    {
        $this->titulo = __('Detalhe da Pessoa');

        $cod_pessoa = (int) $this->getQueryString(name: 'cod_pessoa');

        $objPessoa = new clsPessoaFisica(int_idpes: $cod_pessoa);

        $detalhe = $objPessoa->queryRapida(
            $cod_pessoa,
            'idpes',
            'complemento',
            'nome',
            'cpf',
            'data_nasc',
            'logradouro',
            'idtlog',
            'numero',
            'apartamento',
            'cidade',
            'sigla_uf',
            'cep',
            'ddd_1',
            'fone_1',
            'ddd_2',
            'fone_2',
            'ddd_mov',
            'fone_mov',
            'ddd_fax',
            'fone_fax',
            'email',
            'url',
            'tipo',
            'sexo',
            'zona_localizacao',
            'nome_social'
        );

        $objFoto = new clsCadastroFisicaFoto(idpes: $cod_pessoa);
        $caminhoFoto = $objFoto->detalhe();
        if ($caminhoFoto != false) {
            $this->addDetalhe(detalhe: [
                __('Nome'),
                $detalhe['nome'] . '
                <p><img height="117" src="' . (new UrlPresigner)->getPresignedUrl(url: $caminhoFoto['caminho']) . '"/></p>'
            ]);
        } else {
            $this->addDetalhe(detalhe: [__('Nome'), $detalhe['nome']]);
        }

        if ($detalhe['nome_social']) {
            $this->addDetalhe(detalhe: [__('Nome social e/ou afetivo'), $detalhe['nome_social']]);
        }

        $this->addDetalhe(detalhe: [__('CPF'), int2cpf(int: $detalhe['cpf'])]);

        if ($detalhe['data_nasc']) {
            $this->addDetalhe(detalhe: [__('Data de Nascimento'), dataFromPgToBr(data_original: $detalhe['data_nasc'])]);
        }

        $raca = new clsCadastroFisicaRaca(ref_idpes: $cod_pessoa);
        $raca = $raca->detalhe();
        if (is_array(value: $raca)) {
            $nameRace = LegacyRace::query()
                ->whereKey(id: $raca['ref_cod_raca'])
                ->value(column: 'nm_raca');

            if ($nameRace) {
                $this->addDetalhe(detalhe: [__('Raça'), $nameRace]);
            }
        }

        if ($detalhe['logradouro']) {
            if ($detalhe['numero']) {
                $end = ' nº ' . $detalhe['numero'];
            }

            $this->addDetalhe(detalhe: [__('Endereço'), $detalhe['logradouro'] . ' ' . $end]);
        }

        if ($detalhe['complemento']) {
            $this->addDetalhe(detalhe: [__('Complemento'), $detalhe['complemento']]);
        }

        if ($detalhe['cidade']) {
            $this->addDetalhe(detalhe: [__('Cidade'), $detalhe['cidade']]);
        }

        if ($detalhe['sigla_uf']) {
            $this->addDetalhe(detalhe: [__('Estado'), $detalhe['sigla_uf']]);
        }

        $zona = App_Model_ZonaLocalizacao::getInstance();
        if ($detalhe['zona_localizacao']) {
            $this->addDetalhe(detalhe: [
                __('Zona Localização'),
                $zona->getValue(key: $detalhe['zona_localizacao']),
            ]);
        }

        if ($detalhe['cep']) {
            $this->addDetalhe(detalhe: [__('CEP'), int2cep(int: $detalhe['cep'])]);
        }

        if ($detalhe['fone_1']) {
            $this->addDetalhe(
                detalhe: [__('Telefone 1'), sprintf('(%s) %s', $detalhe['ddd_1'], $detalhe['fone_1'])]
            );
        }

        if ($detalhe['fone_2']) {
            $this->addDetalhe(
                detalhe: [__('Telefone 2'), sprintf('(%s) %s', $detalhe['ddd_2'], $detalhe['fone_2'])]
            );
        }

        if ($detalhe['fone_mov']) {
            $this->addDetalhe(
                detalhe: [__('Celular'), sprintf('(%s) %s', $detalhe['ddd_mov'], $detalhe['fone_mov'])]
            );
        }

        if ($detalhe['fone_fax']) {
            $this->addDetalhe(
                detalhe: [__('Fax'), sprintf('(%s) %s', $detalhe['ddd_fax'], $detalhe['fone_fax'])]
            );
        }

        if ($detalhe['url']) {
            $this->addDetalhe(detalhe: [__('Site'), $detalhe['url']]);
        }

        if ($detalhe['email']) {
            $this->addDetalhe(detalhe: [__('E-mail'), $detalhe['email']]);
        }

        if ($detalhe['sexo']) {
            $this->addDetalhe(detalhe: [__('Sexo'), $detalhe['sexo'] == 'M' ? __('Masculino') : __('Feminino')]);
        }

        $vinculos = collect();
        if ($aluno = LegacyStudent::active()->where('ref_idpes', $cod_pessoa)->first(['cod_aluno'])) {
            $vinculos->push(sprintf(
                '<a target="_blank" href="/intranet/educar_aluno_det.php?cod_aluno=%s">%s</a>',
                $aluno->getKey(),
                __('Aluno')
            ));
        }

        if ($servidor = Employee::active()->find($cod_pessoa, ['cod_servidor', 'ref_cod_instituicao'])) {
            $vinculos->push(sprintf(
                '<a target="_blank" href="/intranet/educar_servidor_det.php?cod_servidor=%s&ref_cod_instituicao=%s">%s</a>',
                $servidor->getKey(),
                $servidor->ref_cod_instituicao,
                __('Servidor')
            ));
        }

        if ($vinculos->isEmpty()) {
            $vinculos->push(__('Pessoa física não possui vínculos'));
        }
        $this->addHtml('<tr><td class="formlttd" width="20%">' . __('Vínculos:') . '</td><td class="formlttd">' . $vinculos->implode('<br>') . '</td></tr>');

        $fileService = new FileService(urlPresigner: new UrlPresigner);
        $files = $fileService->getFiles(relation: LegacyIndividual::find($cod_pessoa));

        if (is_array(value: $files) && count(value: $files) > 0) {
            $this->addHtml(html: view(view: 'uploads.upload-details', data: ['files' => $files])->render());
        }

        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(int_processo_ap: 43, int_idpes_usuario: $this->pessoa_logada, int_soma_nivel_acesso: 7, super_usuario: true)) {
            $this->url_novo = 'atendidos_cad.php';
            $this->url_editar = 'atendidos_cad.php?cod_pessoa_fj=' . $detalhe['idpes'];
        }

        $this->url_cancelar = 'atendidos_lst.php';

        $this->largura = '100%';

        $this->breadcrumb(currentPage: __('Pessoa física'), breadcrumbs: ['educar_pessoas_index.php' => __('Pessoas')]);
    }

    public function Formular()
    {
        $this->title = __('Pessoa');
        $this->processoAp = 43;
    }
};
