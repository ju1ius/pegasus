import RecusriveDescent from './RecursiveDescent'
import MemoEntry from './MemoEntry'
import MemoTable from './MemoTable'

export default class Packrat extends RecusriveDescent {

  /** @type MemoTable[] */
  memoTable = [
    new MemoTable(),
    new MemoTable(),
  ]

  _beforeParse () {
    super._beforeParse()
    this.memoTable = [
      new MemoTable(),
      new MemoTable(),
    ]
  }

  _afterParse (result) {
    super._afterParse(result)
    this.memoTable = null
  }

  _apply (rule) {
    let memo = this._getMemo(rule)
    if (memo) {
      this.pos = memo.end
      return memo.result
    }
    memo = this._setMemo(rule, null)
    const result = this._evaluate(rule)
    memo.result = result
    memo.end = this.pos

    return result
  }

  _cut (position) {
    super._cut(position)
    this.memoTable.forEach(table => table.cut(position))
  }

  /**
   * @param {string} rule
   * @returns {MemoEntry | undefined}
   * @protected
   */
  _getMemo (rule) {
    return this.memoTable[+this.isCapturing].get(this.pos, rule)
  }

  /**
   * @param {string} rule
   * @param result
   * @protected
   */
  _setMemo (rule, result) {
    return this.memoTable[+this.isCapturing].set(this.pos, rule, result)
  }
}
