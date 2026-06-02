from __future__ import annotations

from pathlib import Path

from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import cm
from reportlab.platypus import Paragraph, SimpleDocTemplate, Spacer


def md_to_flowables(md_text: str):
    """
    Minimal Markdown -> ReportLab flowables.
    Supports:
      - Headings: #, ##, ### (mapped to sizes)
      - Horizontal rule: --- (spacer)
      - Paragraphs
      - Bullet lists: '- ' (renders as a paragraph with bullet prefix)
      - Blockquotes: '> ' (italic style)
    """
    styles = getSampleStyleSheet()

    base = ParagraphStyle(
        "Base",
        parent=styles["Normal"],
        fontName="Helvetica",
        fontSize=11,
        leading=15,
        spaceAfter=6,
    )
    h1 = ParagraphStyle(
        "H1",
        parent=base,
        fontSize=20,
        leading=24,
        spaceBefore=6,
        spaceAfter=10,
        fontName="Helvetica-Bold",
    )
    h2 = ParagraphStyle(
        "H2",
        parent=base,
        fontSize=15,
        leading=19,
        spaceBefore=10,
        spaceAfter=8,
        fontName="Helvetica-Bold",
    )
    h3 = ParagraphStyle(
        "H3",
        parent=base,
        fontSize=12.5,
        leading=16,
        spaceBefore=8,
        spaceAfter=6,
        fontName="Helvetica-Bold",
    )
    quote = ParagraphStyle(
        "Quote",
        parent=base,
        fontName="Helvetica-Oblique",
        leftIndent=0.7 * cm,
        textColor="#333333",
    )
    bullet = ParagraphStyle(
        "Bullet",
        parent=base,
        leftIndent=0.6 * cm,
    )

    flow = []

    def add_paragraph(text: str, style: ParagraphStyle):
        # ReportLab uses a simple HTML-like mini-markup.
        safe = (
            text.replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
        )
        flow.append(Paragraph(safe, style))

    lines = md_text.splitlines()
    buf: list[str] = []

    def flush_buf():
        nonlocal buf
        if not buf:
            return
        text = " ".join(s.strip() for s in buf if s.strip())
        if text:
            add_paragraph(text, base)
        buf = []

    for raw in lines:
        line = raw.rstrip()

        if not line.strip():
            flush_buf()
            flow.append(Spacer(1, 6))
            continue

        if line.strip() == "---":
            flush_buf()
            flow.append(Spacer(1, 10))
            continue

        if line.startswith("# "):
            flush_buf()
            add_paragraph(line[2:].strip(), h1)
            continue
        if line.startswith("## "):
            flush_buf()
            add_paragraph(line[3:].strip(), h2)
            continue
        if line.startswith("### "):
            flush_buf()
            add_paragraph(line[4:].strip(), h3)
            continue

        if line.startswith("> "):
            flush_buf()
            add_paragraph(line[2:].strip(), quote)
            continue

        if line.startswith("- "):
            flush_buf()
            add_paragraph("• " + line[2:].strip(), bullet)
            continue

        buf.append(line)

    flush_buf()
    return flow


def main() -> int:
    repo_root = Path(__file__).resolve().parents[1]
    md_path = repo_root / "PROPOSTA_COMERCIAL_GEEK_AO_CUBO.md"
    pdf_path = repo_root / "PROPOSTA_COMERCIAL_GEEK_AO_CUBO.pdf"

    md_text = md_path.read_text(encoding="utf-8")

    doc = SimpleDocTemplate(
        str(pdf_path),
        pagesize=A4,
        leftMargin=2.0 * cm,
        rightMargin=2.0 * cm,
        topMargin=2.0 * cm,
        bottomMargin=2.0 * cm,
        title="Proposta Comercial — Geek ao Cubo",
        author="Geek ao Cubo",
    )

    flow = md_to_flowables(md_text)
    doc.build(flow)
    print(f"OK: wrote {pdf_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

