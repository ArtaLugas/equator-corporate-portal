// ============================================================
// Plugin CKEditor 5 custom: Letter Spacing (jarak antar huruf, inline)
// & Line Height (jarak antar baris, block). GPL — tanpa fitur premium.
// Pola meniru FontSize resmi CKEditor (UIModel + commandName/commandParam).
// ============================================================

import { Plugin, Command, createDropdown, addListToDropdown, UIModel, Collection } from 'ckeditor5';

/**
 * Bangun dropdown toolbar untuk sebuah command + daftar opsi.
 * @param {{name:string,label:string,options:Array<{title:string,value:string|null}>}}
 */
function buildDropdown(editor, { name, label, options }) {
    const command = editor.commands.get(name);

    const itemDefinitions = new Collection();
    for (const option of options) {
        const def = {
            type: 'button',
            model: new UIModel({
                commandName: name,
                commandParam: option.value,
                label: option.title,
                role: 'menuitemradio',
                withText: true,
            }),
        };
        def.model.bind('isOn').to(command, 'value', (value) => String(value ?? '') === String(option.value ?? ''));
        itemDefinitions.add(def);
    }

    editor.ui.componentFactory.add(name, (locale) => {
        const dropdownView = createDropdown(locale);
        addListToDropdown(dropdownView, itemDefinitions, { role: 'menu', ariaLabel: label });

        dropdownView.buttonView.set({ label, tooltip: true, withText: true });
        dropdownView.bind('isEnabled').to(command);

        dropdownView.on('execute', (evt) => {
            editor.execute(evt.source.commandName, { value: evt.source.commandParam });
            editor.editing.view.focus();
        });

        return dropdownView;
    });
}

/* ===================== LETTER SPACING (inline text attribute) ===================== */

class LetterSpacingCommand extends Command {
    refresh() {
        const model = this.editor.model;
        const selection = model.document.selection;
        this.value = selection.getAttribute('letterSpacing') ?? null;
        this.isEnabled = model.schema.checkAttributeInSelection(selection, 'letterSpacing');
    }

    execute({ value = null } = {}) {
        const model = this.editor.model;
        const selection = model.document.selection;

        model.change((writer) => {
            if (selection.isCollapsed) {
                if (value) writer.setSelectionAttribute('letterSpacing', value);
                else writer.removeSelectionAttribute('letterSpacing');
            } else {
                const ranges = model.schema.getValidRanges(selection.getRanges(), 'letterSpacing');
                for (const range of ranges) {
                    if (value) writer.setAttribute('letterSpacing', value, range);
                    else writer.removeAttribute('letterSpacing', range);
                }
            }
        });
    }
}

export class LetterSpacing extends Plugin {
    static get pluginName() {
        return 'LetterSpacing';
    }

    init() {
        const editor = this.editor;

        editor.model.schema.extend('$text', { allowAttributes: 'letterSpacing' });

        editor.conversion.for('downcast').attributeToElement({
            model: 'letterSpacing',
            view: (value, { writer }) => {
                if (!value) return;
                return writer.createAttributeElement('span', { style: `letter-spacing:${value}` }, { priority: 7 });
            },
        });

        editor.conversion.for('upcast').elementToAttribute({
            view: { name: 'span', styles: { 'letter-spacing': /.+/ } },
            model: { key: 'letterSpacing', value: (viewElement) => viewElement.getStyle('letter-spacing') },
        });

        editor.commands.add('letterSpacing', new LetterSpacingCommand(editor));

        buildDropdown(editor, {
            name: 'letterSpacing',
            label: 'Letter spacing',
            options: [
                { title: 'Default', value: null },
                { title: 'Tight (−0.5px)', value: '-0.5px' },
                { title: '0.5px', value: '0.5px' },
                { title: '1px', value: '1px' },
                { title: '2px', value: '2px' },
                { title: '3px', value: '3px' },
            ],
        });
    }
}

/* ===================== LINE HEIGHT (block attribute) ===================== */

class LineHeightCommand extends Command {
    refresh() {
        const model = this.editor.model;
        const block = model.document.selection.getSelectedBlocks().next().value;
        const allowed = block && model.schema.checkAttribute(block, 'lineHeight');
        this.value = allowed ? (block.getAttribute('lineHeight') ?? null) : null;
        this.isEnabled = !!allowed;
    }

    execute({ value = null } = {}) {
        const model = this.editor.model;

        model.change((writer) => {
            const blocks = [...model.document.selection.getSelectedBlocks()].filter((block) =>
                model.schema.checkAttribute(block, 'lineHeight'),
            );
            for (const block of blocks) {
                if (value) writer.setAttribute('lineHeight', value, block);
                else writer.removeAttribute('lineHeight', block);
            }
        });
    }
}

export class LineHeight extends Plugin {
    static get pluginName() {
        return 'LineHeight';
    }

    init() {
        const editor = this.editor;

        editor.model.schema.extend('$block', { allowAttributes: 'lineHeight' });

        // Downcast: tulis style line-height pada elemen blok (pipeline editing & data).
        editor.conversion.for('downcast').add((dispatcher) => {
            dispatcher.on('attribute:lineHeight', (evt, data, api) => {
                if (!api.consumable.consume(data.item, evt.name)) return;
                const viewElement = api.mapper.toViewElement(data.item);
                if (!viewElement) return;
                if (data.attributeNewValue) {
                    api.writer.setStyle('line-height', String(data.attributeNewValue), viewElement);
                } else {
                    api.writer.removeStyle('line-height', viewElement);
                }
            });
        });

        // Upcast: baca style line-height → atribut model.
        editor.conversion.for('upcast').attributeToAttribute({
            view: { styles: { 'line-height': /.+/ } },
            model: { key: 'lineHeight', value: (viewElement) => viewElement.getStyle('line-height') },
        });

        editor.commands.add('lineHeight', new LineHeightCommand(editor));

        buildDropdown(editor, {
            name: 'lineHeight',
            label: 'Line height',
            options: [
                { title: 'Default', value: null },
                { title: '1.0', value: '1' },
                { title: '1.15', value: '1.15' },
                { title: '1.5', value: '1.5' },
                { title: '1.75', value: '1.75' },
                { title: '2.0', value: '2' },
            ],
        });
    }
}
