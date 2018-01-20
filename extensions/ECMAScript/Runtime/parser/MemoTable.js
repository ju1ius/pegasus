import MemoEntry from './MemoEntry'


export default class MemoTable {

  entries = new Map()

  has (pos, rule) {
    if (!this.entries.has(pos)) return false
    return this.entries.get(pos).has(rule)
  }

  get (pos, rule) {
    const rules = this.entries.get(pos)
    if (!rules) return null
    return rules.get(rule) || null
  }

  set (pos, rule, result) {
    const rules = this.entries.get(pos) || new Map()
    const entry = new MemoEntry(pos, result)
    rules.set(rule, entry)
    this.entries.set(pos, rules)

    return entry
  }

  cut (position) {
    this.entries.forEach((rules, pos, entries) => {
      if (pos < position) entries.delete(pos)
    })
  }

}
