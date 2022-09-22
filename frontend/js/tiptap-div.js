import { Node } from 'tiptap'

export class TiptapDiv extends Node {
  get name () {
    return 'div'
  }

  get schema () {
    return {
      attrs: {
        id: {
          default: null
        },
        class: {
          default: null
        },
        style: {
          default: null
        }
      },
      content: 'block*',
      draggable: false,
      group: 'block',
      parseDOM: [{
        tag: 'div',
        getAttrs: dom => ({
          id: dom.getAttribute('id'),
          class: dom.getAttribute('class'),
          style: dom.getAttribute('style')
        })
      }],
      toDOM: node => ['div', node.attrs]
    }
  }
}
