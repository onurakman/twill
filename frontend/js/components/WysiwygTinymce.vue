<template>
  <a17-inputframe :error="error" :note="note" :label="label" :locale="locale" @localize="updateLocale" :size="size"
                  :name="name" :required="required">
    <div class="wysiwyg__outer" :class="textfieldClasses">
      <input :name="name" type="hidden" v-model="value"/>
      <template v-if="editSource">
        <div class="wysiwyg" :class="textfieldClasses" v-show="!activeSource" :dir="dirLocale">
          <div class="wysiwyg__editor" :class="{ 'wysiwyg__editor--limitHeight' : limitHeight }" ref="editorcontainer">
            <editor
              ref="editor"
              :api-key="apiKey"
              :init="editorInitOptions"
              v-model="value"
              model-events="change input"
              @change="textUpdate"
              @input="textUpdate"
            />
          </div>
          <span v-if="shouldShowCounter" class="wysiwyg__limit f--tiny" :class="limitClasses">{{ counter }}</span>
        </div>
<!--        <div class="form__field form__field&#45;&#45;textarea" v-show="activeSource" :dir="dirLocale">-->
<!--          <textarea :placeholder="placeholder" :autofocus="autofocus" v-model="value" @change="updateSourcecode"-->
<!--                    :style="textareaHeight"></textarea>-->
<!--        </div>-->
<!--        <a17-button variant="ghost" @click="toggleSourcecode" class="wysiwyg__button">Source code</a17-button>-->
      </template>
      <template v-else>
        <div class="wysiwyg" :class="textfieldClasses" :dir="dirLocale">
          <div class="wysiwyg__editor" :class="{ 'wysiwyg__editor--limitHeight' : limitHeight }" ref="editorcontainer">
            <editor
              ref="editor"
              :api-key="apiKey"
              :init="editorInitOptions"
              v-model="value"
              model-events="change input"
              @change="textUpdate"
              @input="textUpdate"
            />
          </div>
          <span v-if="shouldShowCounter" class="wysiwyg__limit f--tiny" :class="limitClasses">{{ counter }}</span>
        </div>
      </template>
    </div>
  </a17-inputframe>
</template>

<script>
  import { mapState } from 'vuex'

  import Editor from '@tinymce/tinymce-vue'

  import debounce from 'lodash/debounce'

  import { MEDIA_LIBRARY } from '@/store/mutations'
  import InputMixin from '@/mixins/input'
  import FormStoreMixin from '@/mixins/formStore'
  import InputframeMixin from '@/mixins/inputFrame'
  import LocaleMixin from '@/mixins/locale'

  // Todo: load highligth depending of needs
  // import { loadScript } from '@/utils/loader'
  //
  // const HIGHLIGHT = '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.12.0/build/highlight.min.js'

  export default {
    name: 'A17Wysiwyg',
    mixins: [InputMixin, InputframeMixin, LocaleMixin, FormStoreMixin],
    props: {
      editSource: {
        type: Boolean,
        default: false
      },
      showCounter: {
        type: Boolean,
        default: true
      },
      type: {
        type: String,
        default: 'text'
      },
      prefix: {
        type: String,
        default: ''
      },
      maxlength: {
        type: Number,
        default: 0
      },
      initialValue: {
        default: ''
      },
      limitHeight: {
        type: Boolean,
        default: false
      },
      options: {
        type: Object,
        required: false,
        default: function () {
          return {}
        }
      },
      apiKey: {
        type: String,
        default: ''
      }
    },
    computed: {
      textareaHeight: function () {
        return {
          height: this.editorHeight
        }
      },
      textfieldClasses: function () {
        return {
          'wysiwyg__outer--limitHeight': this.limitHeight,
          's--disabled': this.disabled,
          's--focus': this.focused
        }
      },
      hasMaxlength: function () {
        return this.maxlength > 0
      },
      shouldShowCounter: function () {
        return this.hasMaxlength && this.showCounter
      },
      limitClasses: function () {
        return {
          'wysiwyg__limit--red': this.counter < (this.maxlength * 0.1)
        }
      },
      ...mapState({
        baseUrl: state => state.form.baseUrl,
        selectedMedias: state => state.mediaLibrary.selected,
        mediaType: state => state.mediaLibrary.type
      })
    },
    watch: {
      selectedMedias: function (medias) {
        const editorId = this.$refs.editor.element.id
        if (medias.hasOwnProperty(editorId) && medias[editorId].length > 0) {
          const urlOriginal = medias[editorId][0].original
          if (this.mediaType === 'image') {
            const fullUrl = new URL(urlOriginal)
            const imagePath = urlOriginal.replace(fullUrl.origin, '')
            this.$refs.editor.editor.selection.setContent(`<img src="${imagePath}" alt="" width="" height="" />`)
          } else {
            this.$refs.editor.editor.selection.setContent(`<a href="${urlOriginal}">Change Link</a>`)
          }
          this.$store.commit(MEDIA_LIBRARY.DESTROY_SPECIFIC_MEDIA, {
            name: editorId,
            index: 0
          })
        }
      }
    },
    components: {
      Editor
    },
    data: function () {
      return {
        value: this.initialValue,
        editorHeight: 50,
        toolbarHeight: 52,
        focused: false,
        activeSource: false,
        counter: 0,
        localOptions: {},
        defaultModules: {},
        editorInitOptions: {}
      }
    },
    methods: {
      init () {
        const editorOptions = {
          setup: this.setupTinymce,
          convert_urls: false,
          entity_encoding: 'raw',
          valid_children: '+a[div|span|b|i|u|sup|sub|img|#text|tr|td|label|h1|h2|h3|h4|h5|h6]',
          extended_valid_elements: '*[*]',
          plugins: [
            'preview importcss searchreplace autolink autosave save',
            'directionality code visualblocks visualchars fullscreen',
            'image imagetools link media template codesample table charmap pagebreak',
            'nonbreaking anchor insertdatetime advlist lists',
            'wordcount help charmap quickbars emoticons'
          ],
          menubar: 'edit view insert format tools table',
          toolbar: 'undo redo | fullscreen  preview code imageFromMediaLib fileFromMediaLib | bold italic underline strikethrough | fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | insertfile image imagetools media link anchor codesample | ltr rtl'
        }
        this.editorInitOptions = { ...editorOptions, ...this.options.modules.toolbar }
      },
      updateEditor: function (newValue) {
      },
      updateFromStore: function (newValue) { // called from the formStore mixin
        if (typeof newValue === 'undefined') newValue = ''
        if (this.value !== newValue) {
          this.value = newValue
          this.updateEditor(newValue)
        }
      },
      textUpdate: function () {
        this.preventSubmit()
        this._textUpdateInternal()
      },
      _textUpdateInternal: debounce(function () {
        this.saveIntoStore()
        this.allowSubmit()
      }, 600),
      toggleSourcecode: function () {
        this.editorHeight = (Math.max(50, this.$refs.editor.editor.getBody().scrollHeight) + this.toolbarHeight - 1) + 'px'
        this.activeSource = !this.activeSource
        this.updateSourcecode()
      },
      updateSourcecode: function () {
        this.saveIntoStore() // see formStore mixin
      },
      updateCounter: function (newValue) {
        if (this.showCounter && this.hasMaxlength) {
          this.counter = this.maxlength - newValue
        }
      },
      setupTinymce: function (editor) {
        editor.ui.registry.addButton('imageFromMediaLib', {
          tooltip: 'Image From Media Library',
          icon: 'gallery',
          onAction: this.openImageLib
        })
        editor.ui.registry.addButton('fileFromMediaLib', {
          tooltip: 'File From Media Library',
          icon: 'new-document',
          onAction: this.openFileLib
        })
      },
      openImageLib: function () {
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_CONNECTOR, this.$refs.editor.element.id)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_TYPE, 'image')
        this.$store.commit(MEDIA_LIBRARY.UPDATE_REPLACE_INDEX, -1)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MAX, 1)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MODE, true)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_FILESIZE_MAX, this.filesizeMax || 0)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_WIDTH_MIN, this.widthMin || 0)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_HEIGHT_MIN, this.heightMin || 0)
        if (this.$root.$refs.mediaLibrary) this.$root.$refs.mediaLibrary.open()
      },
      openFileLib: function () {
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_CONNECTOR, this.$refs.editor.element.id)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_TYPE, 'file')
        this.$store.commit(MEDIA_LIBRARY.UPDATE_REPLACE_INDEX, -1)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MAX, 1)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MODE, true)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_FILESIZE_MAX, this.filesizeMax || 0)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_WIDTH_MIN, this.widthMin || 0)
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_HEIGHT_MIN, this.heightMin || 0)
        if (this.$root.$refs.mediaLibrary) this.$root.$refs.mediaLibrary.open()
      }
    },
    beforeMount: function () {
      this.init()
    },
    beforeDestroy: function () {
      this.$refs.editor.editor.destroy()
    }
  }
</script>

<style lang="scss" scoped>
  .wysiwyg__button {
    margin-top: 20px;
  }

  .wysiwyg__outer--limitHeight {
    .wysiwyg {
      position: relative;
      overflow: hidden;
    }
  }

  .wysiwyg__editor--limitHeight {
    max-height: calc(100vh - 250px);
    overflow-y: scroll;
    min-height: 142px;
    border: 1px solid $color__fborder;
    border-top: none;
    scroll-behavior: smooth;
    margin-top: 52px;
    .input--error & {
      border-color: $color__error;
      border-top: none;
    }
    .s--focus & {
      border-color: $color__fborder--hover;
      border-top: none;
    }
  }
</style>
<style lang="scss">
  /* Not scoped style here because we want to overwrite default style of the wysiwig */
  .ql-divider {
    overflow: hidden;
  }

  $height_input: 45px;
  .wysiwyg__limit {
    height: $height_input - 2px;
    line-height: $height_input - 2px;
    color: $color__text--light;
    user-select: none;
    pointer-events: none;
    position: absolute;
    right: 15px;
    bottom: 0;
  }

  .wysiwyg__limit--red {
    color: red;
  }

  /* RTL Direction */
  .wysiwyg[dir='rtl'] .wysiwyg__limit {
    left:15px;
    right:auto;
  }

</style>
<style lang="scss">
  /* Not scoped style here because we want to overwrite default style of the wysiwig */
  .a17 {
    .ql-toolbar.ql-snow {
      border-top-left-radius: 2px;
      border-top-right-radius: 2px;
      background-color: $color__f--bg;
      font-family: inherit;
    }

    .wysiwyg__editor--limitHeight .ql-toolbar {
      z-index: 1;
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
    }

    .ql-editor.ql-blank::before {
      font-style: normal;
      color: $color__f--placeholder;
      @include font-regular;
    }

    .ql-container.ql-snow {
      border-bottom-left-radius: 2px;
      border-bottom-right-radius: 2px;
    }

    .ql-editor {
      background-color: $color__f--bg;
      min-height: 15px * 6;
      caret-color: $color__action;
      color: $color__text--forms;
      overflow: visible;

      &:hover,
      &:focus {
        background: $color__background;
      }
    }

    *[dir='rtl'] .ql-editor {
      direction: rtl;
      text-align: right;
    }

    .wysiwyg__editor--limitHeight .ql-editor {
      min-height: 15px * 10;
    }

    /* Default content styling */
    .ql-snow .ql-editor {
      h1, h2, h3, h4, h5, h6 {
        font-weight: 700;
      }

      b, p b, p strong, strong {
        font-weight:700;
      }

      i, p i, li i, em, p em, li em {
        font-style: italic;
      }

      u, p u, li u {
        text-decoration: underline;
      }

      p, ul, ol, h1, h2, h3, h4, h5 {
        margin-bottom: 1em;
      }

      h1 {
        font-size: 2em;
        line-height: 1.25em;
      }

      h2 {
        font-size: 1.66em;
        line-height: 1.25em;
      }

      h3 {
        font-size: 1.33em;
        line-height: 1.25em;
      }

      h4 {
        font-size: 1.25em;
        line-height: 1.25em;
      }

      h5 {
        font-size: 1em;
        line-height: 1.25em;
      }

      // default code syntax hightlighting is github
      pre {
        color: $color__wysiwyg-codeText;
        padding: 15px;
        overflow: auto;
        background-color: $color__wysiwyg-codeBg;
        border-radius: 3px;
        font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
      }

      sup {
        vertical-align: super;
        font-size: smaller;
      }

      sub {
        vertical-align: sub;
        font-size: smaller;
      }
    }

    .ql-toolbar.ql-snow {
      border-color: $color__fborder;
      border-bottom-color: $color__border--light;
    }

    .ql-container.ql-snow {
      border-color: $color__fborder;
      .wysiwyg__editor--limitHeight {
        border: none;
      }
    }

    .wysiwyg__editor--limitHeight .ql-container.ql-snow {
      border: none;
    }

    .input--error {
      .ql-toolbar.ql-snow {
        border-color: $color__error;
        border-bottom-color: $color__border--light;
      }

      .ql-container.ql-snow {
        border-color: $color__error;
      }

      .wysiwyg__editor--limitHeight .ql-container.ql-snow {
        border: none;
      }
    }

    .s--focus {
      .ql-toolbar.ql-snow {
        border-color: $color__fborder--hover;
        border-bottom-color: $color__border--light;
      }

      .ql-container.ql-snow {
        border-color: $color__fborder--hover;
      }

      .wysiwyg__editor--limitHeight .ql-container.ql-snow {
        border: none;
      }
    }

    .ql-snow a {
      color: $color__link;
    }

    .ql-editor .ql-anchor {
      text-decoration: underline $color__link;
    }

    // Ensure pasting content do not make editor scroll to the top
    // @see https://github.com/quilljs/quill/issues/1374#issuecomment-545112021
    .ql-clipboard {
      position: fixed;
    }

    .ql-snow.ql-toolbar {
      padding: 13px 8px;

      button,
      .ql-align {
        width: 24px;
        margin-right: 35px - 6px - 6px - 6px - 6px;
        text-align: center;
      }

      button.ql-underline {
        top: 1px;
      }

      button.ql-link {
        width: 24px + 9px;
      }

      .icon {
        position: relative;
      }
    }

    .ql-snow.ql-toolbar .ql-formats {
      border-right: 1px solid $color__border--light;

      &:last-child {
        border-right: none;
      }
    }

    .ql-snow.ql-toolbar,
    .ql-snow .ql-toolbar {
      button:hover,
      button:focus,
      button.ql-active,
      .ql-picker-label:hover,
      .ql-picker-label.ql-active,
      .ql-picker-item:hover,
      .ql-picker-item.ql-selected {
        color: $color__link;
      }
    }

    .ql-snow.ql-toolbar,
    .ql-snow .ql-toolbar {
      button:hover .ql-stroke,
      button:focus .ql-stroke,
      button.ql-active .ql-stroke,
      .ql-picker-label:hover .ql-stroke,
      .ql-picker-label.ql-active .ql-stroke,
      .ql-picker-item:hover .ql-stroke,
      .ql-picker-item.ql-selected .ql-stroke,
      button:hover .ql-stroke-miter,
      button:focus .ql-stroke-miter,
      button.ql-active .ql-stroke-miter,
      .ql-picker-label:hover .ql-stroke-miter,
      .ql-picker-label.ql-active .ql-stroke-miter,
      .ql-picker-item:hover .ql-stroke-miter,
      .ql-picker-item.ql-selected .ql-stroke-miter {
        color: $color__link;
      }
    }

    /* dropdown style */
    .ql-toolbar.ql-snow .ql-header,
    .ql-toolbar.ql-snow .ql-size,
    .ql-toolbar.ql-snow .ql-font {
      .ql-picker-label {
        border: 0 none;
        position: relative;
        /*padding-right: 30px;*/

        &::after {
          content: " ";
          position: absolute;
          top: 50%;
          right: 1em;
          z-index: 2;
          position: absolute;
          width: 0;
          height: 0;
          margin-top: -3px;
          border-width: 4px 4px 0;
          border-style: solid;
          border-color: $color__text transparent transparent;
        }

        svg {
          opacity: 0;
        }
      }

      .ql-picker-options {
        background: rgba($color__background, 0.98);
        border-radius: 2px;
        box-shadow: $box-shadow;
        padding: 10px 0;
        border: 0 none;
        margin-top: 6px;

        .ql-picker-item {
          display: block;
          color: $color__text--light;
          padding: 0 15px;
          padding-right: 50px;
          height: 40px;
          line-height: 40px;
          text-decoration: none;
          white-space: nowrap;
          font-family: inherit;

          &:hover {
            color: $color__text;
            background: $color__light;
          }
        }
      }
    }

    .ql-toolbar.ql-snow .ql-picker {
      font-size: 1em;
    }

    .ql-toolbar.ql-snow .ql-picker .ql-picker-label {
      white-space: nowrap;

      &::before {
        line-height: 24px
      }
    }

    .ql-snow .ql-picker.ql-header {
      width: auto;
      min-width: 120px;

      .ql-picker-item,
      .ql-picker-item[data-value="1"],
      .ql-picker-item[data-value="2"],
      .ql-picker-item[data-value="3"],
      .ql-picker-item[data-value="4"],
      .ql-picker-item[data-value="5"] {
        &::before {
          font-weight: normal;
          font-size: 1em;
          white-space: nowrap;
        }
      }
    }
  }
</style>
