
export default class ParseError extends Error {
  constructor (message) {
    super(message)

    this.constructor = ParseError
    this.__proto__   = ParseError.prototype
  }
}
