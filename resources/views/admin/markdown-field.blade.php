<div class="markdown-field" data-markdown-field>
    <label for="markdown-{{ $fieldName }}-{{ $editorId ?? 'page' }}">{{ $fieldLabel }}</label>
    <div class="markdown-toolbar" role="group" aria-label="Formatting">
        <button type="button" data-markdown-prefix="## ">Heading</button>
        <button type="button" data-markdown-prefix="**" data-markdown-suffix="**">Bold</button>
        <button type="button" data-markdown-prefix="*" data-markdown-suffix="*">Italic</button>
        <button type="button" data-markdown-prefix="- ">List</button>
        <button type="button" data-markdown-prefix="[" data-markdown-suffix="](https://)">Link</button>
        <button type="button" data-markdown-prefix="![" data-markdown-suffix="](https://)">Image</button>
        <button type="button" data-markdown-prefix="| Column | Column |&#10;| --- | --- |&#10;| Value | Value |&#10;">Table</button>
    </div>
    <textarea id="markdown-{{ $fieldName }}-{{ $editorId ?? 'page' }}" name="{{ $fieldName }}" rows="10" @required($required ?? false)>{{ $fieldValue }}</textarea>
    <small>Supports Markdown. Raw HTML is removed when the page is displayed.</small>
</div>
