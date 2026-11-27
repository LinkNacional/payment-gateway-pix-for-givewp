/**
 * Harness: carrega o builder real de BR Code (Public/js/lkn-pix.js) em um
 * contexto isolado com stubs mínimos de wp/React e valida o payload gerado.
 *
 * O teste é proposital: o QR que o doador escaneia é montado no navegador.
 * Um CRC16 errado produz um QR inválido e a doação não pode ser paga — e
 * nenhum teste PHP pegaria isso.
 */
const fs = require('fs')
const vm = require('vm')
const path = require('path')

const source = fs.readFileSync(path.join(__dirname, '../../Public/js/lkn-pix.js'), 'utf8')

const stubDoc = {
  querySelector: () => null,
  getElementById: () => null,
  createElement: () => ({ style: {} })
}

const sandbox = {
  console,
  document: stubDoc,
  wp: { i18n: { __: (s) => s }, element: { useEffect: () => {}, useState: (v) => [v, () => {}] } },
  React: { createElement: () => ({}), useState: (v) => [v, () => {}] },
  QRCode: function () {},
  navigator: { clipboard: { writeText: () => {} } },
  alert: () => {},
  lknAttr: { pixType: 'email', pixKey: 'doacao@linknacional.com.br', pixName: 'Link Nacional', pixCity: 'São Paulo', pixId: '', pluginUrl: '' }
}
sandbox.window = {
  parent: { document: stubDoc },
  givewp: { gateways: { register: () => {} }, form: { hooks: { useWatch: () => '' } } }
}
sandbox.globalThis = sandbox

vm.createContext(sandbox)
vm.runInContext(source, sandbox)

const build = sandbox.lknPGPFGGiveWPPixBuilder
const checksum = sandbox.lknPGPFGGiveWPCrcChecksum

if (typeof build !== 'function' || typeof checksum !== 'function') {
  console.error('FALHOU: funções do builder não expostas pelo bundle')
  process.exit(1)
}

/** CRC16/CCITT-FALSE independente, para cruzar com a implementação do plugin. */
function crc16Independent (input) {
  let crc = 0xffff
  for (let i = 0; i < input.length; i++) {
    crc ^= input.charCodeAt(i) << 8
    for (let j = 0; j < 8; j++) {
      crc = (crc & 0x8000) ? ((crc << 1) ^ 0x1021) & 0xffff : (crc << 1) & 0xffff
    }
  }
  return crc.toString(16).toUpperCase().padStart(4, '0')
}

/** Percorre TLVs de um payload EMV e devolve [{id,len,value}]. */
function parseTLV (payload) {
  const out = []
  let i = 0
  while (i < payload.length) {
    const id = payload.slice(i, i + 2)
    const len = parseInt(payload.slice(i + 2, i + 4), 10)
    if (Number.isNaN(len)) {
      throw new Error('TLV com tamanho inválido em ' + i)
    }
    out.push({ id, len, value: payload.slice(i + 4, i + 4 + len) })
    i += 4 + len
  }
  return out
}

const failures = []

const payload = build('10.00')

// 1) CRC calculado bate com implementação independente
const body = payload.slice(0, -4)
const expectedCrc = crc16Independent(body)
const actualCrc = payload.slice(-4)
if (expectedCrc !== actualCrc) {
  failures.push(`CRC divergente: plugin=${actualCrc} independente=${expectedCrc}`)
}

// 2) Todo TLV declara exatamente o tamanho do seu valor (payload escaneável)
try {
  const fields = parseTLV(payload)
  const total = fields.reduce((n, f) => n + 4 + f.len, 0)
  if (total !== payload.length) {
    failures.push(`TLV não fecha: consumiu ${total} de ${payload.length}`)
  }
  const ids = fields.map((f) => f.id)
  for (const required of ['00', '26', '52', '53', '58', '59', '60', '62', '63']) {
    if (!ids.includes(required)) {
      failures.push(`Campo obrigatório ${required} ausente`)
    }
  }
} catch (e) {
  failures.push('Payload não parseável: ' + e.message)
}

// 3) Chave e valor aparecem no payload
if (!payload.includes('doacao@linknacional.com.br')) {
  failures.push('Chave Pix não está no payload')
}
if (!payload.includes('540510.00')) {
  failures.push('Valor 10.00 não está no campo 54')
}

// 4) Sem valor informado, campo 54 não deve existir
const noAmount = build('')
const noAmountFields = parseTLV(noAmount).map((f) => f.id)
if (noAmountFields.includes('54')) {
  failures.push('Campo 54 presente mesmo sem valor informado')
}

// 5) CRC da implementação do plugin bate com a independente (unitário direto)
if (checksum('teste-crc') !== crc16Independent('teste-crc')) {
  failures.push('Função de checksum do plugin diverge da implementação independente')
}

if (failures.length) {
  console.error('FALHAS:\n' + failures.join('\n'))
  process.exit(1)
}

console.log(`OK payload=${payload.length} chars crc=${actualCrc} campos=${parseTLV(payload).length}`)
