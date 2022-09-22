import { Node } from 'tiptap'

export class TiptapDiv extends Node {
  get name () {
    return 'div'
  }

  get schema () {
    return {
      content: 'inline*',
      draggable: false,
      group: 'block',
      parseDOM: [{
        tag: 'div'
      }],
      toDOM () {
        return ['div', 0]
      }
    }
  }
}
