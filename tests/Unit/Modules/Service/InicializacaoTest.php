<?php

class Avaliacao_Service_InicializacaoTest extends Avaliacao_Service_TestCommon
{
    public function test_instancia_lanca_excecao_caso_codigo_de_matricula_nao_seja_informado()
    {
        $this->expectException(\CoreExt_Service_Exception::class);
        new Avaliacao_Service_Boletim;
    }

    public function test_instancia_lanca_excecao_com_opcao_nao_aceita_pela_classe()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Avaliacao_Service_Boletim(['matricula' => 1, 'foo' => 'bar']);
    }

    public function test_dados_de_matricula_inicializados()
    {
        // Método _hydrateComponentes em IedFinder foi alterado. Terá que ser escrito um novo teste
        $this->markTestSkipped();
        $service = $this->_getServiceInstance();
        $options = $service->getOptions();

        $this->assertEquals(
            $this->_getConfigOption('usuario', 'cod_usuario'),
            $options['usuario']
        );

        $this->assertEquals(
            $this->_getConfigOption('matricula', 'aprovado'),
            $options['aprovado']
        );

        $this->assertEquals(
            $this->_getConfigOption('curso', 'hora_falta'),
            $options['cursoHoraFalta']
        );

        $this->assertEquals(
            $this->_getConfigOption('curso', 'carga_horaria'),
            $options['cursoCargaHoraria']
        );

        $this->assertEquals(
            $this->_getConfigOption('serie', 'carga_horaria'),
            $options['serieCargaHoraria']
        );

        $this->assertEquals(
            count($this->_getConfigOptions('anoLetivoModulo')),
            $options['etapas']
        );

        $expected = $this->_getConfigOptions('componenteCurricular');
        $dispensas = $this->_getDispensaDisciplina();
        foreach ($dispensas as $dispensa) {
            unset($expected[$dispensa['ref_cod_disciplina']]);
        }
        $actual = $service->getComponentes();
        $this->assertEquals($expected, $actual);
    }

    public function test_instancia_regra_de_avaliacao_atraves_de_um_numero_de_matricula()
    {
        $service = $this->_getServiceInstance();
        $this->assertInstanceOf('RegraAvaliacao_Model_Regra', $service->getRegra());

        // TabelaArredondamento_Model_Tabela é recuperada através da instância de
        // RegraAvaliacao_Model_Regra
        $this->assertInstanceOf('TabelaArredondamento_Model_Tabela', $service->getRegraAvaliacaoTabelaArredondamento());
    }
}
