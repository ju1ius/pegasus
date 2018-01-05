
export default class NodeTraverser {
  constructor () {
    this.rootNode = null
  }

  traverse (node) {
    this.rootNode = node
    this.beforeTraverse(node)
    let result = this.visit(node)
    result = this.afterTraverse(result)
    this.rootNode = null

    return result
  }

  beforeTraverse (node) {}

  afterTraverse (node) {
    return node
  }

  visit = node => {
    let {name, children} = node

    if (!name) {
      return this.leaveNode(node, children.map(this.visit))
    }

    const enter = `enter_${name}`
    const leave = `leave_${name}`

    if (this[enter]) {
      this[enter](node)
    }
    children = children.map(this.visit)
    if (this[leave]) {
      return this[leave](node, ...children)
    }

    return this.leaveNode(node, children)
  }

  leaveNode (node, children) {
    if (node.isTerminal) {
      if (node.attributes.matches) {
        return node.attributes.matches
      }

      return node.value
    }
    if (node.isQuantifier) {
      if (node.isOptional) {
        return children[0]
      }

      return children
    }
    if (node.isDecorator) {
      return children[0]
    }
    if (children.length === 1) {
      return children[0]
    }

    return children
  }
}
