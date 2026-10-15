#!/usr/bin/env bash
set -euo pipefail

ROOT=$(pwd)
STATIC="$ROOT/static"
CONTENT="$ROOT/content"

TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT

# Find all .html files under static/
find "$STATIC" -type f -name '*.html' | while IFS= read -r html; do
    rel_path="${html#"$STATIC"/}"
    rel_path="${rel_path#./}"
    md_path="$CONTENT/${rel_path%.html}.md"
    mkdir -p "$(dirname "$md_path")"

    # Extract title: first h1 text else filename
    title=$(pandoc -t plain -f html "$html" | head -n1 | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
    if [[ -z "$title" ]]; then
        title=$(basename "${html%.html}" | tr '-' ' ' | awk '{for(i=1;i<=NF;i++) $i=toupper(substr($i,1,1)) substr($i,2)}1')
    fi

    pandoc "$html" -f html -t markdown \
        --wrap=none \
        --extract-media="$TMP" \
        -o "$TMP/temp.md"

    {
        echo "---"
        echo "title: \"$title\""
        echo "date: $(date -u +%Y-%m-%dT%H:%M:%SZ)"
        echo "---"
        echo ""
        cat "$TMP/temp.md"
    } > "$md_path"

    echo "Converted: $html → $md_path"
done

echo "All HTML files converted to Markdown under $CONTENT"