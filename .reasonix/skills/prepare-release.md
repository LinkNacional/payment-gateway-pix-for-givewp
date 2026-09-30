---
name: prepare-release
description: Prepara release do payment-gateway-pix-for-givewp: atualiza versão em README.txt, README.md, CHANGELOG.md, cabeçalho PHP, constante PGPFG_PIX_PLUGIN_VERSION e DEPLOY_TAG dos workflows .yml
---

# prepare-release (payment-gateway-pix-for-givewp)

Atualiza **todos** os arquivos que contêm o número de versão para uma nova release do plugin "Payment Gateway Pix For GiveWP" (`payment-gateway-pix-for-givewp`).

## Parâmetros (via `arguments`)

O usuário pode passar os valores diretamente: `"version=2.3.0 tested_up=7.1 php=8.2 requires_at_least=6.0 highlights=..."`. Se algum valor faltar, pergunte.

- **version** — nova versão (Stable tag)
- **tested_up** — versão do WP testada (Tested up to)
- **php** — versão mínima do PHP (Requires PHP)
- **requires_at_least** — versão mínima do WordPress (Requires at least)
- **highlights** — resumo da versão (fonte primária do changelog). Se vazio, **pergunte** ao usuário o que mudou — NÃO invente a partir do `git log`.

## Fluxo de execução

### 1. Coletar valores

Se não recebidos via arguments, pergunte um por um. Detecte a versão atual:

```
grep -E "Version:|Requires PHP:" payment-gateway-pix-for-givewp.php
grep -E "define\('PGPFG_PIX_PLUGIN_VERSION'" payment-gateway-pix-for-givewp.php
grep -E "^Stable tag:" README.txt
```

Data de hoje: use `date +%d/%m/%Y` (ou API de tempo se o shell não estiver disponível).

### 2. Levantar o contexto das mudanças (changelog)

⚠️ **Regra anti-redundância.** O changelog NUNCA deve listar itens que já pertencem a versões anteriores.

1. **Pergunte ao usuário** o que mudou nesta versão (se `highlights` não veio nos arguments). A resposta dele é a fonte da verdade.
2. O `git log` é **apenas uma dica** — o range de commits costuma estar dessincronizado e traz itens de releases já publicadas.
3. **Leia o topo do changelog atual** (`CHANGELOG.md`) e **descarte** qualquer item já descrito nas entradas anteriores.

```bash
# dica opcional — jamais usar como fonte única
LAST_TAG=$(git describe --tags --abbrev=0 2>/dev/null)
if [ -z "$LAST_TAG" ]; then
    git log -n 10 --oneline
else
    git log ${LAST_TAG}..HEAD --oneline
fi
```

### 3. Atualizar TODOS os arquivos com versão

A versão aparece em **8 locais** distribuídos em **6 arquivos**.

#### 3a. `payment-gateway-pix-for-givewp.php` (raiz)

- `* Version:           X` (cabeçalho do plugin, linha ~19) — **fonte da verdade**
- `* Requires PHP:` se alterado (linha ~24)
- `define('PGPFG_PIX_PLUGIN_VERSION', 'X');` (linha ~46)

#### 3b. `README.txt`

- `Stable tag: X` (linha ~7)
- `Requires at least:`, `Tested up to:` e `Requires PHP:` se alterados (linhas ~5-8)
- Adicionar entrada no topo de `== Changelog ==`, **em inglês**, no formato deste arquivo (`= VERSION - DD/MM/YYYY =` + bullets + linha em branco):
  ```
  = NOVA_VERSION - DD/MM/YYYY =
  * Item baseado nas mudanças

  = VERSAO_ANTERIOR - DD/MM/YYYY =
  ```
- Adicionar a **MESMA** entrada no topo de `== Upgrade Notice ==` (mesmo formato `= VERSION - DD/MM/YYYY =`).

#### 3c. `README.md`

- `* Stable tag: X` (linha ~9). ⚠️ Hoje está **defasado** (`1.0.0`); sincronize com a versão real.

#### 3d. `CHANGELOG.md` (português, com data)

- Adicionar entrada no topo, **em português**, no formato `# VERSION - DD/MM/YYYY`:
  ```
  # NOVA_VERSION - DD/MM/YYYY
  * Item baseado nas mudanças

  # VERSAO_ANTERIOR - DD/MM/YYYY
  ```

#### 3e. `.github/workflows/main.yml`

- `DEPLOY_TAG: "X"` (linha ~11)

#### 3f. `.github/workflows/wordpressRelease.yml`

- `DEPLOY_TAG: "X"` (linha ~9)
- `SLUG` permanece `payment-gateway-pix-for-givewp` (não é versão — não mexer)

## NÃO alterar (pegadinhas deste plugin)

- **Não existe arquivo `-file.php`** neste plugin (diferente de outros da casa). A constante é `PGPFG_PIX_PLUGIN_VERSION`, definida no próprio `payment-gateway-pix-for-givewp.php`.
- `composer.json` / `package.json` **não** guardam a versão do plugin — não editar.
- `languages/*.pot` / `.po` / `.mo` → não guardam a versão do plugin.
- O `README.md` está desatualizado; ao menos o `Stable tag` deve ser sincronizado (o restante do conteúdo é histórico).

## 4. Validação final

Grep com a versão **antiga**:

```
grep -rn "VERSAO_ANTIGA" --include="*.php" --include="*.md" --include="*.txt" --include="*.yml" . --exclude-dir=node_modules --exclude-dir=vendor
```

Esperado: `CHANGELOG.md` e `README.txt` ainda contêm a versão antiga **apenas** nas entradas antigas do changelog. Qualquer outro arquivo retornando a versão antiga é **erro**.

Grep com a versão **nova**:

```
grep -rn "NOVA_VERSAO" --include="*.php" --include="*.md" --include="*.txt" --include="*.yml" . --exclude-dir=node_modules --exclude-dir=vendor
```

Deve retornar no mínimo: `README.txt` (3x: stable tag + changelog + upgrade notice), `CHANGELOG.md` (1x), `README.md` (1x), `.php` raiz (1x no header + 1x na constante), `main.yml` (1x), `wordpressRelease.yml` (1x) = **9 matches** no mínimo.

## 5. Alinhamento com o corpo da release

O corpo das GitHub Releases é gerado por `.github/scripts/generate-release-body.sh` a partir de `README.txt` + `CHANGELOG.md`. Não é preciso editá-lo: ele lê `Stable tag`, `Tested up to`, `Requires PHP` e a entrada correspondente do `CHANGELOG.md` automaticamente.
