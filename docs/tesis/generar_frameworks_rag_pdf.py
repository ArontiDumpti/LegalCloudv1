"""Genera el catálogo PDF de frameworks RAG para LegalCloud AI."""

from pathlib import Path

from fpdf import FPDF

OUT = Path(__file__).with_name("frameworks-rag-legalcloud.pdf")
FONT = Path(r"C:\Windows\Fonts")

NAVY = (11, 31, 58)
ROYAL = (30, 78, 216)
INK = (30, 41, 59)
MUTED = (71, 85, 105)
LINE = (226, 232, 240)
GREEN = (15, 118, 110)
AMBER = (146, 64, 14)
WINE = (157, 23, 77)
GREEN_BG = (236, 253, 245)
AMBER_BG = (255, 247, 237)
WINE_BG = (253, 242, 248)
CARD = (248, 250, 252)

CATS = {
    "Gratis": (GREEN, GREEN_BG),
    "Precio bajo": (AMBER, AMBER_BG),
    "Caro": (WINE, WINE_BG),
}


def entry(name, category, url, why, use):
    return {
        "name": name,
        "category": category,
        "url": url,
        "why": why,
        "use": use,
    }


BEST = [
    entry(
        "Haystack",
        "Gratis",
        "https://haystack.deepset.ai/",
        "Encabeza los gratis porque el pipeline ya trae búsqueda léxica, semántica e híbrida, el modo con el que LegalCloud consulta el fondo.",
        "Recupera pasajes, puede reordenarlos y devolver el origen. Laravel lo llama como servicio y guarda página, versión y hash.",
    ),
    entry(
        "Dify",
        "Precio bajo",
        "https://dify.ai/",
        "Encabeza los de precio bajo porque el flujo se publica como API con una cuota mensual baja, o se instala en tu servidor sin licencia.",
        "El flujo de buscar, citar y responder se arma en un panel. Esa API puede atender la búsqueda y los mensajes del asistente.",
    ),
    entry(
        "NeMo Retriever",
        "Caro",
        "https://docs.nvidia.com/nemo/retriever/latest/",
        "Encabeza los caros porque extrae texto, tablas y figuras del PDF y reordena los pasajes con la pila de NVIDIA.",
        "El costo está en la licencia y en las GPU. Sirve cuando el volumen del fondo y el tiempo de respuesta piden un recuperador empresarial.",
    ),
]

GRATIS = [
    BEST[0],
    entry(
        "LlamaIndex",
        "Gratis",
        "https://www.llamaindex.ai/",
        "Indexa por páginas y devuelve el trozo con su origen.",
        "Sirve para guardar fragmento, página y versión en la evidencia de la consulta.",
    ),
    entry(
        "LLMWare",
        "Gratis",
        "https://github.com/llmware-ai/llmware",
        "Está hecho para contratos y documentos largos, y la respuesta sale con la cita.",
        "Sirve para reglamentos y resoluciones del fondo legal.",
    ),
    entry(
        "RAGFlow",
        "Gratis",
        "https://ragflow.io/",
        "Trocea el PDF con detalle y puede señalar de qué zona salió la cita.",
        "Sirve para reglamentos escaneados, donde importan la página y el párrafo.",
    ),
    entry(
        "LangGraph",
        "Gratis",
        "https://github.com/langchain-ai/langgraph",
        "El turno queda en pasos: buscar, elegir fragmentos, citar y responder.",
        "Sirve cuando la pregunta del consultor no se resuelve con una sola búsqueda.",
    ),
    entry(
        "R2R",
        "Gratis",
        "https://github.com/SciPhi-AI/R2R",
        "Ofrece API de carga, búsqueda y respuesta.",
        "Sirve para emparejarla con la búsqueda y con los mensajes de la conversación.",
    ),
    entry(
        "LangChain",
        "Gratis",
        "https://github.com/langchain-ai/langchain",
        "Arma la cadena de buscar y luego responder.",
        "Laravel la llama como servicio para el asistente sobre el expediente.",
    ),
    entry(
        "LlamaStack",
        "Gratis",
        "https://github.com/meta-llama/llama-stack",
        "Junta recuperación, modelo y evaluación en un solo servicio.",
        "Sirve para dejar el índice y el modelo fuera de la API, como ya está dibujado en el sistema.",
    ),
    entry(
        "DSPy",
        "Gratis",
        "https://github.com/stanfordnlp/dspy",
        "Declaras la pregunta, el contexto y la cita, y él ajusta los prompts con ejemplos.",
        "Sirve para afinar el resumen sustentado y comprobar que usa el reglamento citado.",
    ),
    entry(
        "LightRAG",
        "Gratis",
        "https://github.com/HKUDS/LightRAG",
        "Arma un grafo liviano de entidades y relaciones.",
        "Sirve cuando una norma remite a otra y quieres seguir ese vínculo con pocas llamadas al modelo.",
    ),
    entry(
        "Cognita",
        "Gratis",
        "https://github.com/truefoundry/cognita",
        "Separa la ingesta de documentos y la pregunta en un servicio desplegable.",
        "Sirve para colocar el índice semántico fuera de Laravel.",
    ),
    entry(
        "PrivateGPT",
        "Gratis",
        "https://github.com/zylon-ai/private-gpt",
        "Las preguntas corren contra archivos locales.",
        "Sirve para un fondo reservado que permanece en la red de la organización.",
    ),
    entry(
        "AnythingLLM",
        "Gratis",
        "https://github.com/Mintplex-Labs/anything-llm",
        "Cada expediente puede ser un espacio con sus documentos.",
        "Sirve para ensayar que el consultor solo pregunte sobre lo que tiene asignado.",
    ),
    entry(
        "FastRAG",
        "Gratis",
        "https://github.com/IntelLabs/fastRAG",
        "Son pipelines cortos, pensados para responder con menos espera.",
        "Sirve cuando el mismo expediente se consulta muchas veces.",
    ),
    entry(
        "Kotaemon",
        "Gratis",
        "https://github.com/Cinnamon/kotaemon",
        "La pantalla muestra la respuesta junto al pasaje citado.",
        "Sirve como modelo de interfaz mientras el asistente de Laravel sigue en demostración.",
    ),
    entry(
        "txtai",
        "Gratis",
        "https://github.com/neuml/txtai",
        "Embeddings, búsqueda y respuesta corren en un solo proceso.",
        "Sirve para el piloto del reglamento de ejemplo, en el mismo servidor de la tesis.",
    ),
    entry(
        "Open WebUI",
        "Gratis",
        "https://github.com/open-webui/open-webui",
        "Es un chat con documentos, por espacios de trabajo.",
        "Sirve para que consultor y especialista prueben preguntas antes de conectar la API.",
    ),
    entry(
        "Verba",
        "Gratis",
        "https://github.com/weaviate/Verba",
        "Subes archivos y preguntas, con Weaviate como índice, también en tu servidor.",
        "Sirve para probar la búsqueda semántica sin un contrato.",
    ),
    entry(
        "Semantic Kernel",
        "Gratis",
        "https://github.com/microsoft/semantic-kernel",
        "Orquesta la búsqueda y el modelo desde Python o C#.",
        "Sirve si el servicio de inteligencia se escribe junto a otro sistema de la organización.",
    ),
    entry(
        "Letta",
        "Gratis",
        "https://github.com/letta-ai/letta",
        "Conserva memoria entre los turnos de la charla.",
        "Sirve para la conversación que sigue dentro del mismo expediente.",
    ),
    entry(
        "MaxKB",
        "Gratis",
        "https://github.com/1Panel-dev/MaxKB",
        "Es una base de conocimiento con panel para cargar documentos.",
        "Sirve para que el responsable documental alimente el índice sin escribir código.",
    ),
    entry(
        "UltraRAG",
        "Gratis",
        "https://github.com/OpenBMB/UltraRAG",
        "El pipeline se arma con piezas sueltas.",
        "Sirve para encajar OCR, índice y modelo como sistemas externos.",
    ),
    entry(
        "FlashRAG",
        "Gratis",
        "https://github.com/RUC-NLPIR/FlashRAG",
        "Trae varias técnicas de RAG listas para comparar.",
        "Sirve en la tesis para medir cuál recupera mejor el artículo citado.",
    ),
    entry(
        "Nano-GraphRAG",
        "Gratis",
        "https://github.com/gusye1234/nano-graphrag",
        "Es una versión pequeña y legible del RAG por grafo.",
        "Sirve para un experimento sobre expedientes que se citan entre sí.",
    ),
    entry(
        "Embedchain",
        "Gratis",
        "https://github.com/mem0ai/embedchain-archive",
        "El código queda en un archivo de solo lectura, con licencia Apache 2.0.",
        "Sirve para consultar cómo se armaba un RAG con poca configuración.",
    ),
    entry(
        "Cheshire Cat",
        "Gratis",
        "https://github.com/cheshire-cat-ai/core",
        "Es un núcleo pequeño, con memoria y plugins.",
        "Sirve para un asistente de prueba que recuerde el expediente abierto.",
    ),
]

BAJO = [
    BEST[1],
    entry(
        "Flowise",
        "Precio bajo",
        "https://flowiseai.com/",
        "El pipeline se dibuja en un lienzo y la nube tiene un plan de entrada.",
        "Sirve para revisar el flujo con el especialista sin mantener un proyecto grande.",
    ),
    entry(
        "Canopy",
        "Precio bajo",
        "https://github.com/pinecone-io/canopy",
        "El código es gratis y el índice es Pinecone, con capa gratuita y luego pago por uso.",
        "Sirve para un piloto semántico sin administrar el clúster de vectores.",
    ),
    entry(
        "RAGatouille",
        "Precio bajo",
        "https://github.com/AnswerDotAI/RAGatouille",
        "El software no se cobra y pide una GPU para indexar con ColBERT.",
        "Sirve cuando la búsqueda por palabras del reglamento se queda corta y hace falta una coincidencia más fina.",
    ),
    entry(
        "Superlinked",
        "Precio bajo",
        "https://superlinked.com/",
        "Mezcla el vector con filtros de expediente, categoría y confidencialidad.",
        "La librería no se paga; la nube se cobra por uso y, en un fondo chico, la cuenta se mantiene baja.",
    ),
    entry(
        "Pathway",
        "Precio bajo",
        "https://pathway.com/",
        "En un piloto propio se usa sin comprar licencia. Reindexa el fondo cuando entra una versión nueva.",
        "Si LegalCloud lo vende como producto, la licencia BSL hay que leerla y puede cobrarse.",
    ),
    entry(
        "Quivr",
        "Precio bajo",
        "https://www.quivr.com/",
        "El servicio alojado cobra un plan por la base documental.",
        "Sirve si quieres un archivo consultable por organización sin operar el índice.",
    ),
]

CARO = [
    BEST[2],
    entry(
        "GraphRAG",
        "Caro",
        "https://microsoft.github.io/graphrag/",
        "El código es libre y el gasto está en las llamadas que extraen entidades y relaciones de cada documento.",
        "Sirve para un fondo grande en el que la respuesta depende de cómo se cruzan normas y expedientes.",
    ),
    entry(
        "h2oGPT",
        "Caro",
        "https://github.com/h2oai/h2ogpt",
        "La edición abierta se instala sin licencia. La vía de producción de H2O va por contrato empresarial.",
        "Sirve para una organización que quiere el RAG en su red, con soporte y el despliegue a cargo del proveedor.",
    ),
]


class Catalogo(FPDF):
    def header(self):
        if self.page_no() == 1:
            self.set_fill_color(*NAVY)
            self.rect(0, 0, self.w, 34, "F")
            self.set_xy(16, 7)
            self.set_font("Segoe", "B", 9)
            self.set_text_color(186, 210, 255)
            self.cell(0, 5, "LEGALCLOUD AI", new_x="LMARGIN", new_y="NEXT")
            self.set_x(16)
            self.set_font("Segoe", "B", 18)
            self.set_text_color(255, 255, 255)
            self.cell(0, 9, "Frameworks de arquitectura RAG", new_x="LMARGIN", new_y="NEXT")
            self.set_x(16)
            self.set_font("Segoe", "", 10)
            self.set_text_color(203, 213, 225)
            self.cell(
                0,
                6,
                "Primero, el mejor de cada categoría  ·  14 de octubre de 2026",
                new_x="LMARGIN",
                new_y="NEXT",
            )
            self.set_top_margin(42)
            self.set_y(42)
        else:
            self.set_top_margin(18)
            self.set_y(7)
            self.set_font("Segoe", "", 9)
            self.set_text_color(*MUTED)
            self.cell(0, 5, "LegalCloud AI  ·  Frameworks de arquitectura RAG", align="L")
            self.set_draw_color(*LINE)
            self.line(16, 13.5, self.w - 16, 13.5)
            self.set_y(18)

    def footer(self):
        self.set_y(-12)
        self.set_font("Segoe", "", 9)
        self.set_text_color(*MUTED)
        self.cell(0, 6, str(self.page_no()), align="C")


def wrapped_height(pdf, width, line_h, text, style="", size=10):
    pdf.set_font("Segoe", style, size)
    return pdf.multi_cell(width, line_h, text, dry_run=True, output="HEIGHT")


def section_title(pdf, title, blurb):
    blurb_h = wrapped_height(pdf, pdf.epw, 5, blurb, size=10)
    if pdf.get_y() + 14 + blurb_h + 40 > pdf.h - 16:
        pdf.add_page()
    pdf.ln(1)
    pdf.set_font("Segoe", "B", 14)
    pdf.set_text_color(*NAVY)
    pdf.cell(0, 8, title, new_x="LMARGIN", new_y="NEXT")
    pdf.set_draw_color(*ROYAL)
    pdf.set_line_width(0.5)
    y = pdf.get_y()
    pdf.line(pdf.l_margin, y, pdf.w - pdf.r_margin, y)
    pdf.ln(4)
    pdf.start_section(title, level=0)


def draw_card(pdf, item, number, featured=False):
    color, bg = CATS[item["category"]]
    width = pdf.epw
    text_w = width - 12
    why_h = wrapped_height(pdf, text_w, 5, item["why"], size=10)
    use_h = wrapped_height(pdf, text_w, 5, item["use"], size=10)
    height = 8 + 7 + why_h + 1.5 + use_h + 6 + 7
    if pdf.get_y() + height > pdf.h - 16:
        pdf.add_page()

    x = pdf.l_margin
    y = pdf.get_y()
    pdf.set_fill_color(*bg)
    pdf.set_draw_color(*LINE)
    pdf.rect(x, y, width, height, "FD")
    pdf.set_fill_color(*color)
    pdf.rect(x, y, 1.6, height, "F")

    pdf.set_xy(x + 5, y + 3.5)
    pdf.set_font("Segoe", "B", 11)
    pdf.set_text_color(*NAVY)
    label = f"{number}   {item['name']}"
    name_w = pdf.get_string_width(label) + 2
    pdf.cell(name_w, 6, label)

    chip = item["category"].upper()
    pdf.set_font("Segoe", "B", 8)
    chip_w = pdf.get_string_width(chip) + 6
    pdf.set_fill_color(*color)
    pdf.set_text_color(255, 255, 255)
    pdf.cell(chip_w, 6, chip, align="C", fill=True)

    pdf.set_xy(x + 5, y + 12)
    pdf.set_font("Segoe", "", 10)
    pdf.set_text_color(*INK)
    pdf.multi_cell(text_w, 5, item["why"], new_x="LMARGIN", new_y="NEXT")
    pdf.set_x(x + 5)
    pdf.multi_cell(text_w, 5, item["use"], new_x="LMARGIN", new_y="NEXT")
    pdf.ln(1.2)
    pdf.set_x(x + 5)
    pdf.set_font("Segoe", "", 9)
    pdf.set_text_color(*ROYAL)
    pdf.cell(text_w, 5, item["url"], link=item["url"])
    pdf.set_y(y + height + 3)


def draw_list(pdf, items):
    for index, item in enumerate(items, start=1):
        draw_card(pdf, item, index)


def main():
    pdf = Catalogo(format="A4", unit="mm")
    pdf.set_auto_page_break(auto=True, margin=16)
    pdf.set_margins(16, 16, 16)
    pdf.add_font("Segoe", "", str(FONT / "segoeui.ttf"))
    pdf.add_font("Segoe", "B", str(FONT / "segoeuib.ttf"))
    pdf.add_font("Segoe", "I", str(FONT / "segoeuii.ttf"))
    pdf.set_title("Frameworks de arquitectura RAG para LegalCloud AI")
    pdf.set_author("LegalCloud AI")
    pdf.set_lang("es")
    pdf.set_creator("LegalCloud AI")
    pdf.add_page()

    pdf.set_font("Segoe", "", 11)
    pdf.set_text_color(*INK)
    intro = (
        "Catálogo de 36 frameworks para la arquitectura RAG de LegalCloud AI. "
        "El orden abre con el mejor de cada categoría, elegido para la búsqueda híbrida, "
        "las citas con página y versión, y un servicio que Laravel llama desde fuera de la API. "
        "Dentro de cada lista, el número 1 es ese mismo framework. "
        "La licencia gratuita se instala en tu servidor. El precio bajo es un plan chico, pago por uso o una GPU. "
        "Lo caro es licencia empresarial, GPU de NVIDIA o muchas llamadas al modelo al indexar. "
        "Si el modelo no es local, sus tokens se pagan en los tres grupos."
    )
    pdf.multi_cell(0, 5.5, intro)
    pdf.ln(3)

    best_blurb = (
        "Este es el orden de arranque: primero el mejor gratis, después el mejor de precio bajo "
        "y al final el mejor de los caros."
    )
    section_title(pdf, "1. Los mejores de cada categoría", best_blurb)
    pdf.set_font("Segoe", "", 10)
    pdf.set_text_color(*MUTED)
    pdf.multi_cell(0, 5, best_blurb)
    pdf.ln(3)
    for index, item in enumerate(BEST, start=1):
        draw_card(pdf, item, index, featured=True)

    gratis_blurb = "Se instalan en tu servidor. No hay tarifa de licencia. Haystack va primero."
    section_title(pdf, "2. Gratis", gratis_blurb)
    pdf.set_font("Segoe", "", 10)
    pdf.set_text_color(*MUTED)
    pdf.multi_cell(0, 5, gratis_blurb)
    pdf.ln(3)
    draw_list(pdf, GRATIS)

    bajo_blurb = (
        "El código se puede instalar sin licencia. El camino cómodo cobra un plan chico, "
        "el uso del índice o una sola GPU. Dify va primero."
    )
    section_title(pdf, "3. Precio bajo", bajo_blurb)
    pdf.set_font("Segoe", "", 10)
    pdf.set_text_color(*MUTED)
    pdf.multi_cell(0, 5, bajo_blurb)
    pdf.ln(3)
    draw_list(pdf, BAJO)

    caro_blurb = (
        "El costo está en la licencia, en las GPU o en la cantidad de llamadas al modelo al indexar. "
        "NeMo Retriever va primero."
    )
    section_title(pdf, "4. Caros", caro_blurb)
    pdf.set_font("Segoe", "", 10)
    pdf.set_text_color(*MUTED)
    pdf.multi_cell(0, 5, caro_blurb)
    pdf.ln(3)
    draw_list(pdf, CARO)

    pdf.output(str(OUT))
    print(OUT)


if __name__ == "__main__":
    main()
