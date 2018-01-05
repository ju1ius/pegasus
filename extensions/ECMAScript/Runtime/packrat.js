import MemoEntry from './memo'
import {ParseError, IncompleteParseError} from './exceptions'

export default class Packrat {

  constructor () {
    /** @type {Map<String, MemoEntry>} */
    this.memos = new Map()
    /** @var string */
    this.startRule = null
  }

  /**
   *
   * @param text
   * @param startRule
   */
  parseAll (text, startRule = this.startRule) {
    const result = this.parse(text, 0, startRule)
    if (this.pos < this.text.length) {
      throw new IncompleteParseError(this.text, this.pos, this.error)
    }

    return result
  }

  parse (text, pos, startRule = this.startRule) {
    this.text = text
    this.pos = pos
    this.capturing = true
    this.error = new ParseError(text)
    this.memos = []

    const result = this.apply(startRule)
    if (!result) {
      throw this.error
    }
    this.memos = this.error = null

    return result
  }

  apply (ruleName) {
    let memo = this.getMemo(ruleName, this.pos)
    if (memo) {
      this.pos = memo.end
      return memo.result
    }
    memo = new MemoEntry(null, this.pos)
    this.setMemo(ruleName, this.pos, memo)
    const result = this.evaluate(ruleName)
    memo.result = result
    memo.end = this.pos

    return result
  }

  evaluate (ruleName) {
    return this[`match_${ruleName}`]()
  }

  getMemo (ruleName, pos) {
    return this.memos.get(`${ruleName}:${this.capturing}:${pos}`)
  }

  setMemo (ruleName, pos, memo) {
    this.memos.set(`${ruleName}:${this.capturing}:${pos}`, memo)
  }
}
