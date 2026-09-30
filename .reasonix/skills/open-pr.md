---
name: open-pr
description: Abre PR de dev → main no payment-gateway-pix-for-givewp no padrão Link Nacional (título VERSION - repo(resumo); corpo com metadados e CHANGELOG)
---

# open-pr (payment-gateway-pix-for-givewp)

Abre um Pull Request de `dev` → `main` via `gh pr create`, no padrão Link Nacional, para o repositório `payment-gateway-pix-for-givewp`.

## Parâmetros (via `arguments`)

O usuário pode passar: `version=2.2.8 tested_up=7.0 summary=Ajuste no QR Code`. Qualquer valor ausente é extraído do código.

- **version** — versão da release (Stable tag / cabeçalho PHP)
- **tested_up** — WP testado até
- **summary** — resumo CURTO usado no TÍTULO. Se ausente, derive da entrada mais recente do `CHANGELOG.md` (NÃO do `git log`).

## Fluxo de execução

### 1. Extrair metadados (se não vierem nos arguments)

```bash
# Cabeçalho PHP (fonte da verdade da versão)
grep -m1 -E "^\s*\*\s*Version:" payment-gateway-pix-for-givewp.php
grep -m1 -E "^\s*\*\s*Requires PHP:" payment-gateway-pix-for-givewp.php

# README.txt (Tested up to / Stable tag)
grep -m1 -i "^Tested up to:" README.txt
grep -m1 -i "^Stable tag:" README.txt

# Nome do repositório no GitHub (NÃO usar basename $PWD)
REPO_NAME=$(basename -s .git "$(git config --get remote.origin.url)")
# → payment-gateway-pix-for-givewp
```

### 2. Ler o changelog da versão atual (fonte do resumo e dos bullets)

⚠️ **Regra anti-redundância.** NÃO use `git log` para gerar o resumo — o range de commits está dessincronizado e traz itens de versões já publicadas. Leia a entrada mais recente do changelog:

```bash
# Preferir CHANGELOG.md (português). Fallback: README.txt, seção == Changelog ==.
head -n 20 CHANGELOG.md
```

A entrada mais recente tem o formato `# VERSION - DD/MM/AA` seguido de bullets `* ...`.

- **TÍTULO**: resuma esses bullets em uma frase curta (≤ ~12 palavras).
- **CORPO (seção CHANGELOG)**: copie os bullets das `CHANGELOG.md` (português), sem hash e sem reescrever.

> Dica: o corpo do PR é o **mesmo** conteúdo do release body gerado pelo workflow. Para reproduzi-lo localmente:
> `bash .github/scripts/generate-release-body.sh`

### 3. Montar TÍTULO

Formato exato (obrigatório) — **sem espaço antes do parêntese**:

```
VERSION - REPO_NAME(RESUMO_CURTO)
```

Exemplo:

```
2.2.8 - payment-gateway-pix-for-givewp(Ajuste na geração do QR Code Pix)
```

### 4. Montar CORPO

Use exatamente este gabarito. Os campos de cabeçalho saem do `README.txt`/cabeçalho PHP; a Descrição e a Instalação são boilerplate fixo; só o CHANGELOG e a versão variam:

```markdown
# Payment Gateway Pix For GiveWP
Contribuidores: linknacional
Link: https://www.linknacional.com.br/
Tags: gateway, payments, givewp, pix, give
Testado até: {TESTED_UP}
Versão estável: {VERSION}
Licença: GPLv3
URI da Licença: http://www.gnu.org/licenses/gpl-3.0.html
Traduções: Português(Brasil) / Inglês

Adicione pagamentos via Pix (o sistema de pagamento instantâneo) aos formulários de doação do GiveWP.

## Descrição

Otimize seu processo de doação e alcance mais doadores brasileiros integrando o PIX, o sistema de pagamento instantâneo, aos formulários de doação do GiveWP.

**Recursos**

* **Aumente as doações:** ofereça aos doadores brasileiros o método de pagamento preferido deles, elevando os valores de contribuição e a conversão.
* **Sem taxas altas:** dispense as taxas de cartão de crédito e aproveite o custo mais baixo das transações PIX.
* **Doações instantâneas:** o valor cai na sua conta em tempo real, melhorando a satisfação e incentivando novas contribuições.
* **Segurança:** aproveite a infraestrutura robusta do PIX para transações seguras.
* **Integração transparente:** funciona perfeitamente com o GiveWP, tornando a configuração e o fluxo de doação simples.

**Dependências**

Este plugin depende do [GiveWP](https://wordpress.org/plugins/give/) para funcionar.

**Instruções de uso**

1. Procure na barra lateral do WordPress a área de plugins.
2. Na lista de plugins instalados, use a opção "adicionar novo" no topo.
3. Envie o arquivo `payment-gateway-pix-for-givewp.zip`.
4. Clique em "Instalar Agora" e depois ative o plugin.

Pronto! Seus doadores poderão doar via Pix.

## Instalação

1. Baixe o plugin.
2. No painel administrativo do WordPress, vá para Plugins > Adicionar Novo.
3. Clique em "Enviar Plugin" e selecione o arquivo ZIP do plugin que você baixou.
4. Clique em "Instalar Agora" e, em seguida, em "Ativar Plugin".
5. Certifique-se de que o GiveWP também está ativado.

## CHANGELOG:

{BULLETS copiados da entrada mais recente do CHANGELOG.md, no formato "* Item". NÃO invente a partir do git log.}
```

### 5. Abrir o PR

Sempre `dev` → `main`:

```bash
gh pr create \
  --base main \
  --head dev \
  --title "VERSION - REPO_NAME(RESUMO_CURTO)" \
  --body "$(cat <<'EOF'
...corpo...
EOF
)"
```

### 6. Confirmar

Mostre a URL retornada pelo `gh` e o comando usado. Se o PR já existir para `dev` → `main`, o `gh` vai avisar — não force `--force` sem pedir.

## Regras

- **Nunca** edite arquivos do repo para abrir o PR (é só `gh pr create`).
- Título SEMPRE no formato `VERSION - payment-gateway-pix-for-givewp(resumo)`, **sem espaço antes do parêntese**.
- `REPO_NAME` é o nome do repositório no GitHub (`payment-gateway-pix-for-givewp`) — derive via `git config --get remote.origin.url`, não de `basename $PWD`.
- Corpo SEMPRE com o cabeçalho de metadados, Descrição, Instalação e a seção `## CHANGELOG:` com os bullets da versão.
- Se `version` / `tested_up` divergirem entre o cabeçalho PHP e o `README.txt`, use o **cabeçalho PHP** e avise.
- Nunca inclua hashes de commit no corpo.
- Bullets do corpo SEMPRE vindos da entrada mais recente do `CHANGELOG.md` — nunca do `git log`.
