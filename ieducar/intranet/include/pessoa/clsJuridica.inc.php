<?php

class clsJuridica
{
    public $idpes;

    public $idpes_cad;

    public $idpes_rev;

    public $cnpj;

    public $fantasia;

    public $insc_estadual;

    public $capital_social;

    public $codUsuario;

    public $tabela;

    public $schema;

    /**
     * Construtor
     *
     * @return Object:clsEstadoCivil
     */
    public function __construct($idpes = false, $cnpj = false, $fantasia = false, $insc_estadual = false, $capital_social = false, $idpes_cad = false, $idpes_rev = false)
    {
        $objPessoa = new clsPessoa_($idpes);
        if ($objPessoa->detalhe()) {
            $this->idpes = $idpes;
        }

        if (config('legacy.app.uppercase_names')) {
            $fantasia = Str::upper($fantasia);
        }

        $this->fantasia = $fantasia;
        $this->cnpj = $cnpj;
        $this->insc_estadual = $insc_estadual;
        $this->capital_social = $capital_social;
        $this->idpes_cad = $idpes_cad ? $idpes_cad : \Illuminate\Support\Facades\Auth::id();
        $this->idpes_rev = $idpes_rev ? $idpes_rev : \Illuminate\Support\Facades\Auth::id();

        $this->tabela = 'juridica';
        $this->schema = 'cadastro';
    }

    /**
     * Funcao que cadastra um novo registro com os valores atuais
     *
     * @return bool
     */
    public function cadastra()
    {
        $db = new clsBanco;

        if (is_numeric($this->idpes) && is_numeric($this->idpes_cad)) {
            $campos = '';
            $valores = '';
            if ($this->fantasia) {
                $fantasia = $db->escapeString($this->fantasia);
                $campos .= ', fantasia';
                $valores .= ", '{$fantasia}'";
            }
            if (is_numeric($this->insc_estadual)) {
                $campos .= ', insc_estadual';
                $valores .= ", '{$this->insc_estadual}' ";
            }
            if (is_string($this->capital_social)) {
                $campos .= ', capital_social';
                $valores .= ", '{$this->capital_social}' ";
            }

            /**
             * Quando o CNPJ é null é preciso montar um insert específico por conta da concatenação com NULL
             */
            if ($this->cnpj === null) {
                $sql = "INSERT INTO {$this->schema}.{$this->tabela} (idpes, cnpj, origem_gravacao, data_cad, operacao, idpes_cad $campos) VALUES ($this->idpes, null, 'M', NOW(), 'I', '$this->idpes_cad' $valores)";

            } else {
                $sql = "INSERT INTO {$this->schema}.{$this->tabela} (idpes, cnpj, origem_gravacao, data_cad, operacao, idpes_cad $campos) VALUES ($this->idpes, '$this->cnpj', 'M', NOW(), 'I', '$this->idpes_cad' $valores)";
            }

            $db->Consulta($sql);

            if ($this->idpes) {
                $this->detalhe();
            }

            return true;
        }

        return false;
    }

    /**
     * Edita o registro atual
     *
     * @return bool
     */
    public function edita()
    {
        $db = new clsBanco;

        if (is_numeric($this->idpes) && is_numeric($this->idpes_rev)) {
            $set = [];
            if (is_string($this->fantasia)) {
                $fantasia = $db->escapeString($this->fantasia);
                $set[] = " fantasia = '{$fantasia}' ";
            }

            if (is_numeric($this->insc_estadual)) {
                if ($this->insc_estadual) {
                    $set[] = " insc_estadual = '{$this->insc_estadual}' ";
                } else {
                    $set[] = ' insc_estadual = NULL ';
                }
            } else {
                $set[] = ' insc_estadual = NULL ';
            }

            if (is_string($this->capital_social)) {
                $set[] = " capital_social = '{$this->capital_social}' ";
            }

            if ($this->idpes_rev) {
                $set[] = " idpes_rev = '{$this->idpes_rev}' ";
            }

            if (is_numeric($this->cnpj)) {
                $set[] = " cnpj = '{$this->cnpj}' ";
            } else {
                $set[] = ' cnpj = NULL ';
            }

            if ($set) {
                $campos = implode(', ', $set);
                $this->detalhe();
                $db->Consulta("UPDATE {$this->schema}.{$this->tabela} SET $campos WHERE idpes = '$this->idpes' ");

                return true;
            }
        }

        return false;
    }

    /**
     * Remove o registro atual
     *
     * @return bool
     */
    public function exclui()
    {
        if (is_numeric($this->idpes)) {
            $db = new clsBanco;
            $this->detalhe();
            $db->Consulta("DELETE FROM {$this->schema}.{$this->tabela} WHERE idpes = {$this->idpes}");

            return true;
        }

        return false;
    }

    /**
     * Exibe uma lista baseada nos parametros de filtragem passados
     *
     * @return array|false
     */
    public function lista($str_fantasia = false, $str_insc_estadual = false, $int_cnpj = false, $str_ordenacao = false, $int_limite_ini = false, $int_limite_qtd = false, $arrayint_idisin = false, $arrayint_idnotin = false, $int_idpes = false)
    {
        $db = new clsBanco;
        $where = '';
        $whereAnd = 'WHERE ';
        if (is_string($str_fantasia)) {
            $str_fantasia = $db->escapeString($str_fantasia);
            $where .= "{$whereAnd} (fcn_upper_nrm(j.fantasia) LIKE fcn_upper_nrm('%$str_fantasia%') OR fcn_upper_nrm(nome) LIKE fcn_upper_nrm('%$str_fantasia%'))";
            $whereAnd = ' AND ';
        }
        if (is_string($str_insc_estadual)) {
            $where .= "{$whereAnd}insc_estadual ILIKE  '%$str_insc_estadual%'";
            $whereAnd = ' AND ';
        }
        if (is_numeric($int_idpes)) {
            $where .= "{$whereAnd}j.idpes = '$int_idpes'";
            $whereAnd = ' AND ';
        }
        if ($this->codUsuario) {
            $where .= "{$whereAnd}j.idpes IN (SELECT ref_idpes
                                              FROM pmieducar.escola
                                             INNER JOIN pmieducar.escola_usuario ON (escola_usuario.ref_cod_escola = escola.cod_escola)
                                             WHERE ref_cod_usuario = $this->codUsuario
                                               AND escola.ativo = 1)";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_cnpj)) {
            $i = 0;
            while (substr($int_cnpj, $i, 1) == 0) {
                $i++;
            }
            if ($i > 0) {
                $int_cnpj = substr($int_cnpj, $i);
            }
            $where .= "{$whereAnd} cnpj::varchar ILIKE  '%$int_cnpj%' ";
            $whereAnd = ' AND ';
        }

        if (is_array($arrayint_idisin)) {
            $ok = true;
            foreach ($arrayint_idisin as $val) {
                if (!is_numeric($val)) {
                    $ok = false;
                }
            }
            if ($ok) {
                $where .= "{$whereAnd}j.idpes IN ( " . implode(',', $arrayint_idisin) . ' )';
                $whereAnd = ' AND ';
            }
        }

        if (is_array($arrayint_idnotin)) {
            $ok = true;
            foreach ($arrayint_idnotin as $val) {
                if (!is_numeric($val)) {
                    $ok = false;
                }
            }
            if ($ok) {
                $where .= "{$whereAnd}idpes NOT IN ( " . implode(',', $arrayint_idnotin) . ' )';
                $whereAnd = ' AND ';
            }
        }

        $orderBy = '';
        if (is_string($str_ordenacao)) {
            $orderBy = "ORDER BY $str_ordenacao";
        }
        $limit = '';
        if ($int_limite_ini !== false && $int_limite_qtd !== false) {
            $limit = " LIMIT $int_limite_ini,$int_limite_qtd";
        }

        $db = new clsBanco;
        $db->Consulta("SELECT COUNT(0) AS total FROM cadastro.juridica j INNER JOIN cadastro.pessoa pessoa ON pessoa.idpes = j.idpes $where");
        $db->ProximoRegistro();
        $total = $db->Campo('total');
        $db->Consulta("SELECT j.idpes, j.cnpj, j.fantasia, j.insc_estadual, j.capital_social FROM cadastro.juridica j INNER JOIN cadastro.pessoa pessoa ON pessoa.idpes = j.idpes $where $orderBy $limit");
        $resultado = [];
        while ($db->ProximoRegistro()) {
            $tupla = $db->Tupla();
            $tupla['total'] = $total;
            $resultado[] = $tupla;
        }
        if (count($resultado)) {
            return $resultado;
        }

        return false;
    }

    /**
     * Retorna um array com os detalhes do objeto
     *
     * @return array|false
     */
    public function detalhe()
    {
        if ($this->idpes) {
            $db = new clsBanco;
            $db->Consulta("SELECT idpes, cnpj, fantasia, insc_estadual, capital_social FROM {$this->schema}.{$this->tabela} WHERE idpes = {$this->idpes}");
            if ($db->ProximoRegistro()) {
                return $db->Tupla();
            }
        } elseif ($this->cnpj) {
            $db = new clsBanco;
            $db->Consulta("SELECT idpes, cnpj, fantasia, insc_estadual, capital_social FROM {$this->schema}.{$this->tabela} WHERE cnpj = {$this->cnpj}");
            if ($db->ProximoRegistro()) {
                return $db->Tupla();
            }
        }

        return false;
    }
}
