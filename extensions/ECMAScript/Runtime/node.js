
export default class Node {
  constructor (name, start, end, value, children = [], attributes = {}) {
    this.name = name
    this.value = value
    this.start = start
    this.end = end
    this.children = children
    this.attributes = attributes

    this.isTerminal = false
    this.isComposite = false
    this.isDecorator = false
    this.isQuantifier = false
    this.isOptional = false
  }

  static terminal (name, start, end, value, attributes = {}) {
    const node = new Node(name, start, end, value, null, attributes)
    node.isTerminal = true
    return node
  }

  static composite (name, start, end, children = [], attributes = {}) {
    const node = new Node(name, start, end, null, children, attributes)
    node.isComposite = true
    return node
  }

  static decorator (name, start, end, child, attributes = {}) {
    const node = new Node(name, start, end, null, [child], attributes)
    node.isDecorator = true
    return node
  }

  static quantifier (name, start, end, children = [], optional = false) {
    const node = new Node(name, start, end, null, children)
    node.isQuantifier = true
    node.isOptional = optional
    return node
  }
}
