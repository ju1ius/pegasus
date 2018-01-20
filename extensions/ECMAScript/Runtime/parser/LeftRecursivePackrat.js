import MemoEntry from './MemoEntry'
import Packrat from './Packrat'


class Head {
  /**
   * @param {string} ruleName
   */
  constructor (ruleName) {
    this.rule = ruleName
    this.involved = new Map()
    this.eval = new Map()
  }

  /**
   * @param {string} ruleName
   *
   * @returns {boolean}
   */
  involves (ruleName) {
    return this.rule === ruleName || this.involved.has(ruleName)
  }
}


class LeftRecursion {
  /**
   * @param {string} ruleName
   * @param {CSTNode} [seed]
   * @param {Head} [head]
   */
  constructor (ruleName, seed, head) {
    this.rule = ruleName
    this.seed = seed
    this.head = head
  }
}


export default class LeftRecursivePackrat extends Packrat {

  heads = new Map()
  lrStack = []
  isGrowingSeedParse = false

  constructor () {
    super()
  }

  _beforeParse () {
    super._beforeParse()
    this.heads = new Map()
    this.lrStack = []
  }

  _afterParse (result) {
    super._afterParse(result)
    this.heads = this.lrStack = null
  }

  _cut (position) {
    this.cutStack.pop()
    this.cutStack.push(true)
    if (this.isGrowingSeedParse) return
    this.memoTable.forEach(table => table.cut(position))
  }

  _apply (rule) {
    const {pos} = this
    let memo = this._recall(rule)
    if (!memo) {
      const lr = new LeftRecursion(rule)
      this.lrStack.push(lr)
      memo = this._setMemo(rule, lr)
      const result = this.evaluate(rule)
      this.lrStack.pop()
      memo.end = this.pos
      if (!lr.head) {
        memo.result = result

        return result
      }
      lr.seed = result

      return this._leftRecursionAnswer(rule, pos, memo)
    }

    this.pos = memo.end
    if (memo instanceof LeftRecursion) {
      this._setupLeftRecursion(rule, memo.result)

      return memo.seed
    }

    return memo.result
  }

  /**
   * @param {string} rule
   * @param {LeftRecursion} lr
   */
  _setupLeftRecursion (rule, lr) {
    const {lrStack} = this
    if (!lr.head) {
      lr.head = new Head(rule)
    }
    for (let i = 0, l = lrStack.length, item; i < l; i++) {
      item = lrStack[i]
      if (item.head === lr.head) return
      lr.head.involved.set(item.rule, item.rule)
    }
  }

  /**
   * @param {string} rule
   * @param {number} pos
   * @param {MemoEntry} memo
   *
   * @returns {CSTNode|LeftRecursion|null}
   */
  _leftRecursionAnswer (rule, pos, memo) {
    const {head, seed} = memo.result
    if (head.rule !== rule) return seed
    memo.result = seed
    if (!memo.result) return null

    return this._growSeedParse(rule, pos, memo, head)
  }

  /**
   * @param {string} rule
   * @param {number} pos
   * @param {MemoEntry} memo
   * @param {Head} head
   *
   * @returns {CSTNode|LeftRecursion|null}
   */
  _growSeedParse (rule, pos, memo, head) {
    this.isGrowingSeedParse = true
    this.heads.set(pos, head)
    while (true) {
      this.pos = pos
      head.eval = head.involved
      const result = this.evaluate(rule)
      if (!result || this.pos <= memo.end) {
        break
      }
      memo.result = result
      memo.end = this.pos
    }
    this.heads.delete(pos)
    this.pos = memo.end
    this.isGrowingSeedParse = false

    return memo.result
  }

  /**
   * @param {string} rule
   *
   * @returns {MemoEntry|null}
   */
  _recall (rule) {
    const {pos} = this
    const memo = this._getMemo(rule)
    const head = this.heads.get(pos)

    if (!head) {
      return memo
    }
    if (!memo && !head.involves(rule)) {
      return new MemoEntry(pos, null)
    }
    if (head.eval.has(rule)) {
      head.eval.delete(rule)
      memo.result = this.evaluate(rule)
      memo.end = this.pos
    }

    return memo
  }
}
