import groovy.json.JsonOutput
import groovy.json.JsonSlurper
import groovy.xml.XmlUtil
import java.time.Instant
import java.time.temporal.ChronoUnit

def projectFile = new File(mockOperation.mockService.project.path)
def baseDir = projectFile.parentFile
def dataFile = new File(baseDir, 'datos.json')
def queryFile = new File(baseDir, 'consultas.json')

def request = new XmlSlurper(false, false).parseText(mockRequest.requestContent)
def operation = request.'**'.find { it.name().toString().endsWith('buscar') }

def field = { String name ->
    operation?.children()?.find { it.name().toString().endsWith(name) }?.text()?.trim() ?: ''
}

def text = field('texto')
def category = field('categoria')
def dateFrom = field('fechaDesde')
def now = Instant.now()
def expires = now.plus(7, ChronoUnit.DAYS)

def documents = new JsonSlurper().parse(dataFile, 'UTF-8')
def results = documents.findAll { document ->
    def matchesText = !text || document.titulo.toLowerCase().contains(text.toLowerCase()) ||
        document.descripcion.toLowerCase().contains(text.toLowerCase())
    def matchesCategory = !category || document.categoria.equalsIgnoreCase(category)
    def matchesDate = !dateFrom || document.fechaPublicacion >= dateFrom
    matchesText && matchesCategory && matchesDate
}

def savedQueries = queryFile.exists() && queryFile.text.trim() ?
    new JsonSlurper().parse(queryFile, 'UTF-8') : []

def activeQueries = savedQueries.findAll { query ->
    Instant.parse(query.expiraEn).isAfter(now)
}

def queryId = UUID.randomUUID().toString()
activeQueries << [
    id: queryId,
    texto: text,
    categoria: category,
    fechaDesde: dateFrom,
    cantidadResultados: results.size(),
    creadaEn: now.toString(),
    expiraEn: expires.toString()
]

queryFile.setText(JsonOutput.prettyPrint(JsonOutput.toJson(activeQueries)), 'UTF-8')

def esc = { value -> XmlUtil.escapeXml(value?.toString() ?: '') }
def resultXml = results.collect { document ->
    """<bus:resultado>
        <bus:id>${document.id}</bus:id>
        <bus:titulo>${esc(document.titulo)}</bus:titulo>
        <bus:descripcion>${esc(document.descripcion)}</bus:descripcion>
        <bus:categoria>${esc(document.categoria)}</bus:categoria>
        <bus:fechaPublicacion>${document.fechaPublicacion}</bus:fechaPublicacion>
      </bus:resultado>"""
}.join('\n')

requestContext.responseXml = """<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bus="http://motorbusqueda/">
  <soapenv:Header/>
  <soapenv:Body>
    <bus:buscarResponse>
      <bus:consultaId>${queryId}</bus:consultaId>
      <bus:cantidad>${results.size()}</bus:cantidad>
      <bus:expiraEn>${expires}</bus:expiraEn>
      <bus:resultados>
        ${resultXml}
      </bus:resultados>
    </bus:buscarResponse>
  </soapenv:Body>
</soapenv:Envelope>"""

log.info "Consulta ${queryId}: ${results.size()} resultado(s); expira ${expires}"
return 'RespuestaBusqueda'
