<script setup lang="ts">
import { EditorState } from '@codemirror/state';
import { Decoration, type DecorationSet, EditorView, keymap, lineNumbers, MatchDecorator, ViewPlugin, type ViewUpdate } from '@codemirror/view';
import { defaultKeymap, history, historyKeymap } from '@codemirror/commands';
import { searchKeymap, highlightSelectionMatches } from '@codemirror/search';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const model = defineModel<string>({ required: true });
const editorRoot = ref<HTMLElement | null>(null);
let editor: EditorView | null = null;

const envCommentMatcher = new MatchDecorator({
    regexp: /^#.*/g,
    decoration: Decoration.mark({ class: 'env-comment' }),
});

const envCommentHighlighter = ViewPlugin.fromClass(
    class {
        decorations: DecorationSet;

        constructor(view: EditorView) {
            this.decorations = envCommentMatcher.createDeco(view);
        }

        update(update: ViewUpdate) {
            this.decorations = envCommentMatcher.updateDeco(update, this.decorations);
        }
    },
    {
        decorations: (value) => value.decorations,
    },
);

onMounted(() => {
    if (!editorRoot.value) {
        return;
    }

    editor = new EditorView({
        parent: editorRoot.value,
        state: EditorState.create({
            doc: model.value,
            extensions: [
                lineNumbers(),
                history(),
                highlightSelectionMatches(),
                envCommentHighlighter,
                keymap.of([...defaultKeymap, ...historyKeymap, ...searchKeymap]),
                EditorView.lineWrapping,
                EditorView.updateListener.of((update) => {
                    if (update.docChanged) {
                        model.value = update.state.doc.toString();
                    }
                }),
            ],
        }),
    });
});

watch(model, (value) => {
    if (!editor || editor.state.doc.toString() === value) {
        return;
    }

    editor.dispatch({
        changes: { from: 0, to: editor.state.doc.length, insert: value },
    });
});

onBeforeUnmount(() => editor?.destroy());
</script>

<template>
    <div ref="editorRoot" class="env-editor" />
</template>
