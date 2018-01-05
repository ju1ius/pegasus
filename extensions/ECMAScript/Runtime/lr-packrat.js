import MemoEntry from './memo'
import Packrat from './packrat'

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
   * @param {Node} [seed]
   * @param {Head} [head]
   */
  constructor (ruleName, seed, head) {
    this.rule = ruleName
    this.seed = seed
    this.head = head
  }
}

export default class LeftRecursivePackrat extends Packrat {
  constructor () {
    super()
    this.heads = new Map()
    this.lrStack = []
  }

  parse (text, pos, startRule = this.startRule) {
    this.heads = new Map()
    this.lrStack = []

    const result = super.parse(text, pos, startRule)

    this.heads = this.lrStack = null

    return result
  }

  apply (ruleName) {
    const {pos} = this
    let memo = this.recall(ruleName)
    if (!memo) {
      const lr = new LeftRecursion(ruleName)
      this.lrStack.push(lr)
      memo = new MemoEntry(lr, pos)
      this.setMemo(ruleName, pos, memo)
      const result = this.evaluate(ruleName)
      this.lrStack.pop()
      memo.end = this.pos
      if (!lr.head) {
        memo.result = result

        return result
      }
      lr.seed = result

      return this.leftRecursionAnswer(ruleName, pos, memo)
    }

    this.pos = memo.end
    if (memo instanceof LeftRecursion) {
      this.setupLeftRecursion(ruleName, memo.result)

      return memo.seed
    }

    return memo.result
  }

  /**
   * @param {string} ruleName
   * @param {LeftRecursion} lr
   */
  setupLeftRecursion (ruleName, lr) {
    const {lrStack} = this
    if (!lr.head) {
      lr.head = new Head(ruleName)
    }
    for (let i = 0, l = lrStack.length, item; i < l; i++) {
      item = lrStack[i]
      if (item.head === lr.head) return
      lr.head.involved.set(item.rule, item.rule)
    }
  }

  /**
   * @param {string} ruleName
   * @param {number} pos
   * @param {MemoEntry} memo
   *
   * @returns {Node|LeftRecursion|null}
   */
  leftRecursionAnswer (ruleName, pos, memo) {
    const {head, seed} = memo.result
    if (head.rule !== ruleName) return seed
    memo.result = seed
    if (!memo.result) return null

    return this.growSeedParse(ruleName, pos, memo, head)
  }

  /**
   * @param {string} ruleName
   * @param {number} pos
   * @param {MemoEntry} memo
   * @param {Head} head
   *
   * @returns {Node|LeftRecursion|null}
   */
  growSeedParse (ruleName, pos, memo, head) {
    this.heads.set(pos, head)
    while (true) {
      this.pos = pos
      head.eval = head.involved
      const result = this.evaluate(ruleName)
      if (!result || this.pos <= memo.end) {
        break
      }
      memo.result = result
      memo.end = this.pos
    }
    this.heads.delete(pos)
    this.pos = memo.end

    return memo.result
  }

  /**
   * @param {string} ruleName
   *
   * @returns {MemoEntry|null}
   */
  recall (ruleName) {
    const {pos} = this
    const memo = this.getMemo(ruleName, pos)
    const head = this.heads.get(pos)

    if (!head) {
      return memo
    }
    if (!memo && !head.involves(ruleName)) {
      return new MemoEntry(null, pos)
    }
    if (head.eval.has(ruleName)) {
      head.eval.delete(ruleName)
      memo.result = this.evaluate(ruleName)
      memo.end = this.pos
    }

    return memo
  }
}
