<?php

declare(strict_types=1);

namespace Testes;

use Lab\Modelos\RepositorioAmostras;
use PHPUnit\Framework\TestCase;

final class RepositorioAmostrasTest extends TestCase
{
    private string $arquivo;

    protected function setUp(): void
    {
        $this->arquivo = sys_get_temp_dir() . '/amostras-teste-' . uniqid() . '.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->arquivo)) {
            unlink($this->arquivo);
        }
    }

    public function testArquivoInexistenteRetornaListaVazia(): void
    {
        $repositorio = new RepositorioAmostras($this->arquivo);
        $this->assertSame([], $repositorio->listar());
    }

    public function testSalvarEListarAmostras(): void
    {
        $repositorio = new RepositorioAmostras($this->arquivo);
        $repositorio->salvar(['antes' => ['ph' => 7.0], 'parecer_depois' => 'POTAVEL']);
        $repositorio->salvar(['antes' => ['ph' => 6.5], 'parecer_depois' => 'ALERTA']);

        $amostras = $repositorio->listar();
        $this->assertCount(2, $amostras);
        $this->assertSame('POTAVEL', $amostras[0]['parecer_depois']);
        $this->assertSame('ALERTA', $amostras[1]['parecer_depois']);
    }
}