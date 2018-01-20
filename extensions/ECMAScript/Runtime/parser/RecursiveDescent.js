import ParseError from './ParseError'


export default class RecursiveDescent {

  pos = 0
  startRule = null
  isCapturing = true
  rightmostFailurePosition = 0
  rightmostFailures = new Map()
  cutStack = []

  parse (text, startRule = null) {
    this.isCapturing = true
    return this._parse(text, 0, startRule, false)
  }

  partialParse (text, pos = 0, startRule = null) {
    this.isCapturing = true
    return this._parse(text, pos, startRule, true)
  }

  _parse (text, startPos, startRule = null, allowPartial = false) {
    this.source = text
    this.pos = startPos
    startRule = startRule || this.startRule
    this.rightmostFailurePosition = 0
    this.rightmostFailures = new Map()
    this.cutStack = []

    this._beforeParse()
    const result = this._apply(startRule)
    const parsedFully = this.pos === text.length

    if (!result || (!parsedFully && !allowPartial)) {
      this._afterParse(result)
      throw new ParseError()
    }

    this._afterParse()
  }

  _beforeParse () {
    this.cutStack.push(false)
  }

  _afterParse (result) {}

  _apply (rule) {
    return this._evaluate(rule)
  }

  _evaluate (rule) {
    return this[`match_${rule}`]()
  }

  _cut () {
    this.cutStack.pop()
    this.cutStack.push(true)
  }

  _registerFailure (rule, expr, pos) {
    if (pos > this.rightmostFailurePosition) {
      this.rightmostFailurePosition = pos
      const rightmostFailures = this.rightmostFailures.get(pos) || []
      rightmostFailures.push({rule, expr, pos})
      this.rightmostFailures.set(pos, rightmostFailures)
    }
  }
}
