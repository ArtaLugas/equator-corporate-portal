import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Heading,
    Font,
    FontColor,
    FontBackgroundColor,
    Alignment,
    Link,
    List,
    ListProperties,
    TodoList,
    BlockQuote,
    Table,
    TableToolbar,
    Image,
    ImageToolbar,
    ImageCaption,
    ImageStyle,
    ImageResize,
    ImageUpload,
    MediaEmbed,
    HorizontalLine,
    RemoveFormat,
    SourceEditing,
    PasteFromOffice,
    Autoformat,
} from 'ckeditor5';
import { LetterSpacing, LineHeight } from './ckeditor-spacing';

window.editors = {};

window.initCkEditor = async (element) => {
    if (!element) return;

    if (window.editors[element.id]) return;

    try {
        const editor = await ClassicEditor.create(element, {
            licenseKey: 'GPL',

            plugins: [
                Essentials,
                Paragraph,

                Bold,
                Italic,
                Underline,
                Strikethrough,

                Heading,

                Font,
                FontColor,
                FontBackgroundColor,

                Alignment,

                LetterSpacing,
                LineHeight,

                Link,

                List,
                ListProperties,
                TodoList,

                BlockQuote,

                Table,
                TableToolbar,

                Image,
                ImageToolbar,
                ImageCaption,
                ImageStyle,
                ImageResize,
                ImageUpload,

                MediaEmbed,

                HorizontalLine,

                RemoveFormat,

                SourceEditing,

                PasteFromOffice,

                Autoformat,
            ],

            toolbar: {
                items: [
                    'undo',
                    'redo',

                    '|',

                    'heading',

                    '|',

                    'bold',
                    'italic',
                    'underline',
                    'strikethrough',

                    '|',

                    'fontColor',
                    'fontBackgroundColor',

                    '|',

                    'alignment',
                    'lineHeight',
                    'letterSpacing',

                    '|',

                    'bulletedList',
                    'numberedList',
                    'todoList',

                    '|',

                    'link',

                    'insertTable',

                    'blockQuote',

                    'horizontalLine',

                    '|',

                    'mediaEmbed',
                    'insertImage',
                    'imageTextAlternative',
                    '|',
                    'imageStyle:inline',
                    'imageStyle:block',
                    'imageStyle:side',

                    '|',

                    'removeFormat',

                    '|',

                    'sourceEditing',
                ],
            },

            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
            },

            list: {
                properties: {
                    styles: true,      // choose bullet/number style
                    startIndex: true,  // set starting number
                    reversed: true,    // reverse numbering
                },
            },
        });

        window.editors[element.id] = editor;
    } catch (error) {
        console.error('CKEditor Init Error:', error);
    }
};
