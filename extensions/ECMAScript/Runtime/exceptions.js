
export class ParseError extends Error {
  constructor (text, position, expr = null, rule = '') {
    super()
    this.text = text
    this.position = position
    this.expr = expr
    this.rule = rule
    // Make `instanceof` work when this code is transpiled to ES5
    this.constructor = ParseError
    this.__proto__   = ParseError.prototype
  }
}

export class IncompleteParseError extends ParseError {
  constructor (text, position, error) {
    super(text, position)
    this.parseError = error
  }
}