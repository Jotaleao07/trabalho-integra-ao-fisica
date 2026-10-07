# Laboratório Digital de Qualidade da Água — ODS 6

Aplicação web em PHP que simula um laboratório de análise de qualidade da água.
O usuário informa as medições feitas **antes** e **depois** de um biofiltro
experimental; o sistema classifica cada parâmetro segundo padrões de
potabilidade, emite um parecer final sobre a amostra e calcula a eficiência
do filtro. Toda a lógica de cálculo é validada por testes unitários com PHPUnit.

Trabalho interárea relacionado aos conceitos de Química e Biologia vistos em
sala (padrões de potabilidade, tratamento de água) e ao **ODS 6 — Água potável
e saneamento**.

## Requisitos

- PHP >= 8.4
- Composer
- Laravel Herd (ou o servidor embutido do PHP)

## Instalação e execução

```bash
composer install
```

**Com Laravel Herd:** aponte o site para a pasta do projeto (onde está o
`index.php`) e acesse o endereço `.test` gerado pelo Herd.

**Sem o Herd (servidor embutido):**

```bash
php -S localhost:8000
```

Abra `http://localhost:8000` no navegador.

## Como rodar os testes

```bash
composer install
vendor/bin/phpunit --testdox
```

Para ver a cobertura de código (meta: pelo menos 80% das classes de cálculo):

```bash
vendor/bin/phpunit --coverage-text
```

A cobertura é medida sobre `src/Modelos` (os algoritmos). A interface web não
entra na meta de cobertura.

## Algoritmos implementados

### `src/Modelos/ClassificadorAgua.php`

Classifica cada parâmetro e gera o parecer final da amostra:

| Parâmetro | Potável | Alerta | Não potável |
|---|---|---|---|
| pH | 6,0 a 9,5 | — | fora da faixa |
| Turbidez | ≤ 5 uT | — | > 5 uT |
| Cloro residual | 0,2 a 2 mg/L | 2 a 5 mg/L | < 0,2 ou > 5 mg/L |
| Dureza | ≤ 500 mg/L | — | > 500 mg/L |
| Temperatura | ≤ 25 °C | 26 a 30 °C | > 30 °C |
| Sólidos dissolvidos totais | ≤ 500 mg/L | 501 a 1000 mg/L | > 1000 mg/L |

O parecer final é `NAO_POTAVEL` se qualquer parâmetro estiver fora, `ALERTA`
se algum estiver em alerta, e `POTAVEL` caso contrário.

### `src/Modelos/Biofiltro.php`

Modelo matemático do biofiltro experimental:

- `taxaRemocao($antes, $depois)` — taxa de remoção em %: `(antes − depois) / antes × 100`;
- `aplicarTaxa($concentracao, $taxa)` — aplica uma taxa de remoção a uma concentração;
- `simularCamadas($concentracao, [$taxa1, $taxa2, ...])` — simula a passagem por várias camadas do filtro;
- `eficienciaPorParametro($antes, $depois)` — taxa de remoção de cada parâmetro medido;
- `rotuloEficiencia($taxa)` — classifica a eficiência como piora, baixa, moderada ou alta.

### `src/Modelos/RepositorioAmostras.php`

Grava cada amostra analisada em `dados/amostras.json`, formando o dataset do
projeto. A interface também permite baixar os dados em CSV.

## Testes

Cada algoritmo tem sua classe de teste em `tests/`, cobrindo:

- **casos felizes** — valores dentro da faixa de potabilidade;
- **casos de borda** — valores exatamente no limite (ex.: pH 6,0 e 9,5);
- **casos de erro** — valores fisicamente impossíveis, divisão por zero na
  taxa de remoção e campos ausentes;
- **eficiência do biofiltro** — taxas calculadas manualmente e conferidas no teste;
- **integração** — classificação dos parâmetros combinada com o parecer final.

## Dataset

Os dados submetidos pelo formulário são gravados em `dados/amostras.json`.
Antes da entrega, insira nesse arquivo o dataset real coletado pela equipe —
nenhum dado fictício deve ser usado.

## Referências

- [Portaria GM/MS nº 888/2021](https://bvsms.saude.gov.br/bvs/saudelegis/gm/2021/prt0888_07_05_2021.html) — padrões de potabilidade no Brasil;
- [Diretrizes da OMS para qualidade da água potável](https://www.who.int/publications/i/item/9789241549950).

As faixas de temperatura e de sólidos totais são critérios didáticos do
projeto e devem ser discutidas no relatório técnico.

## Estrutura do projeto

```
index.php              Página principal (entrada da aplicação)
exportar.php           Exporta o dataset em CSV
src/Modelos/           Algoritmos de classificação, biofiltro e persistência
src/Controle/          Processamento do formulário e exportação
src/visao/pagina.php   Interface web (HTML + Bootstrap)
tests/                 Testes unitários (PHPUnit)
dados/                 Dataset das amostras analisadas
```
