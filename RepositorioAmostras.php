<?php

declare(strict_types=1);

namespace Lab\Modelos;

/**
 * Guarda as amostras analisadas em um arquivo JSON, formando o dataset
 * de dados reais coletados pela equipe.
 */
final class RepositorioAmostras
{
    public function __construct(private readonly string $arquivo)
    {
    }

    public function salvar(array $amostra): void
    {
        $amostras = $this->listar();
        $amostras[] = $amostra;
        if (!is_dir(dirname($this->arquivo))) {
            mkdir(dirname($this->arquivo), 0777, true);
        }
        file_put_contents(
            $this->arquivo,
            json_encode($amostras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /** @return list<array> */
    public function listar(): array
    {
        if (!is_file($this->arquivo) || filesize($this->arquivo) === 0) {
            return [];
        }
        $dados = json_decode((string) file_get_contents($this->arquivo), true);
        if (!is_array($dados)) {
            return [];
        }
        // Aceita tanto uma amostra unica quanto a lista de amostras.
        return isset($dados['antes']) ? [$dados] : $dados;
    }
}
