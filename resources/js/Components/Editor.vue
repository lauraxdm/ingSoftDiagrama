<template>
  <ul class="float-tool-bar">
    <li class="tool-btn" @click="zoomIn" title="Acercar">
      <i class="fa-solid fa-plus"></i>
    </li>
    <li class="tool-btn" @click="zoomOut" title="Alejar">
      <i class="fa-solid fa-minus"></i>
    </li>
    <li class="tool-btn" @click="undo" title="Deshacer">
      <i class="fa-solid fa-arrow-rotate-left"></i>
    </li>
    <li class="tool-btn" @click="redo" title="Volver">
      <i class="fa-solid fa-arrow-rotate-right"></i>
    </li>
    <li class="tool-btn" @click="clearCanvas" title="Limpiar">
      <i class="fa-solid fa-trash-can"></i>
    </li>
  </ul>

  <div class="grid text-sm">
    <div class="grid grid-cols-12 mb-4">
      <div class="col-span-4">
        <div class="flex gap-4 overflow-x-scroll">
          <UserAvatar
            v-for="(user, index) in onlineUsers"
            :user="user"
            :key="index"
          >
          </UserAvatar>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-4 w-full">
    <div class="col col-span-1">
      <!-- Elementos del contenedor -->
      <div class="row">
        <div
          id="myPaletteDiv"
          class="flex"
          style="
            width: 100%;
            height: 500px;
            margin-right: 10px;
            box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
            background: rgba(0, 0, 0, 0.03);
          "
        ></div>
      </div>
      <div class="row pt-5 pr-5">
        <ul class="grid w-full gap-2 md:grid-rows-2">
          <!-- Flechas -->
          <li>
            <input
              type="radio"
              id="arrow_triangle"
              name="synchronous"
              value="yes"
              v-model="synchronous"
              class="hidden peer"
              required
            />
            <label
              for="arrow_triangle"
              class="inline-flex items-center justify-center w-full py-2 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100"
            >

              <img
                :src="'../images/arrow_triangle.png'"
                alt="open arrow"
                height="10"
                width="180"
              />
            </label>
          </li>
          <li>
            <input
              type="radio"
              id="open_arrow_triangle"
              name="synchronous"
              value="no"
              v-model="synchronous"
              class="hidden peer"
            />
            <label
              for="open_arrow_triangle"
              class="inline-flex items-center justify-center w-full py-2 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100"
            >
              <img
                :src="'../images/arrow_open_triangle.png'"
                alt="open arrow"
                height="10"
                width="180"
              />
            </label>
          </li>
        </ul>
      </div>
    </div>
    <!-- Main del Diagrama -->
    <div class="col col-span-3">
      <div
        id="goDiagramDiv"
        style="
          width: 100%;
          height: 670px;
          border: solid 1px rgba(43, 52, 65, 0.4);
          background: rgba(0, 0, 0, 0.05);
        "
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from "vue";
import go from "gojs";

import UserAvatar from "@/Components/UserAvatar.vue";

const $ = go.GraphObject.make;
const synchronous = ref("yes");
let myDiagram;
let modelData;
let onlineUsers = ref([]);

const LinePrefix = 20;
const LineSuffix = 30;
const MessageSpacing = 20;
const ActivityWidth = 10;
const ActivityStart = 5;
const ActivityEnd = 5;
const lineV = {
  figure: "LineV",
  fill: null,
  stroke: "gray",
  strokeDashArray: [3, 3],
  width: 20,
  alignment: go.Spot.Center,
  portId: "",
  fromLinkable: true,
  fromLinkableDuplicates: true,
  toLinkable: true,
  toLinkableDuplicates: true,
  cursor: "pointer",
};

const props = defineProps({
  diagram: {
    type: Object,
    default: () => {},
  },
  project: { type: Object },
});

onMounted(async () => {
  connectSocket();
  initEditor();
  initPalette();
});

// El código anterior define una clase personalizada llamada "MessageLink" que extiende la clase "go.Link" en
// el marco HTML de Vue. Esta clase personalizada se utiliza para personalizar la apariencia y el comportamiento de los enlaces
// en un diagrama
class MessageLink extends go.Link {
  constructor() {
    super();
    this.time = 0;
    this.direction = 0;
  }

  // Devuelve el punto en el que se debe crear el enlace.
  getLinkPoint(node, port, spot, from, ortho, othernode, otherport) {
    const p = port.getDocumentPoint(go.Spot.Center);
    const r = port.getDocumentBounds();
    const op = otherport.getDocumentPoint(go.Spot.Center);

    const data = this.data;
    const time = data !== null ? data.time : this.time; // if not bound, assume this has its own "time" property

    const aw = this.findActivityWidth(node, time);
    const x = op.x > p.x ? p.x + aw / 2 : p.x - aw / 2;
    const y = Math.max(convertTimeToY(time), 180);
    return new go.Point(x, y);
  }

  findActivityWidth(node, time) {
    let aw = ActivityWidth;
    if (node instanceof go.Group) {
      // vea si hay un nodo de actividad en este punto; si no, conecte el enlace directamente con la línea de vida del Grupo
      if (
        !node.memberParts.any((mem) => {
          const act = mem.data;
          return (
            act !== null &&
            act.start <= time &&
            time <= act.start + act.duration
          );
        })
      ) {
        aw = 0;
      }
    }
    return aw;
  }

  // Devuelve 0 si apunta a la derecha y 180 si apunta a la izquierda
  getLinkDirection(
    node,
    port,
    linkpoint,
    spot,
    from,
    ortho,
    othernode,
    otherport
  ) {
    const p = port.getDocumentPoint(go.Spot.Center);
    const op = otherport.getDocumentPoint(go.Spot.Center);
    const right = op.x > p.x;
    return right ? 0 : 180;
  }

  computePoints() {
    if (this.fromNode === this.toNode) {
      // también manejar un enlace reflexivo como un loop ortogonal simple
      const data = this.data;
      const time = data !== null ? data.time : this.time; // if not bound, assume this has its own "time" property
      const p = this.fromNode.port.getDocumentPoint(go.Spot.Center);
      const aw = this.findActivityWidth(this.fromNode, time);

      const x = p.x + aw / 2;
      const y = convertTimeToY(time);
      this.clearPoints();
      this.addPoint(new go.Point(x, y));
      this.addPoint(new go.Point(x + 50, y));
      this.addPoint(new go.Point(x + 50, y + 5));
      this.addPoint(new go.Point(x, y + 5));
      return true;
    } else {
      return super.computePoints();
    }
  }
}

class MessagingTool extends go.LinkingTool {
  constructor() {
    super();

    const $ = go.GraphObject.make;

    this.temporaryLink = $(
      MessageLink,
      $(go.Shape, "Rectangle", { stroke: "magenta", strokeWidth: 2 })
    );
  }

  doActivate() {
    super.doActivate();
    const time = convertYToTime(this.diagram.firstInput.documentPoint.y);
    this.temporaryLink.time = Math.ceil(time); // round up to an integer value
  }

  insertLink(fromnode, fromport, tonode, toport) {
    const newlink = super.insertLink(fromnode, fromport, tonode, toport);

    if (newlink !== null) {
      const model = this.diagram.model;
      // specify the time of the message
      const start = this.temporaryLink.time;
      const duration = 1;
      newlink.data.time = start;

      model.setDataProperty(newlink.data, "text", "msg");
      model.setDataProperty(newlink.data, "synchronous", synchronous.value);
      // and create a new Activity node data in the "to" group data
      const newact = {
        group: newlink.data.to,
        start: start,
        duration: duration,
      };

      model.addNodeData(newact);
      // now make sure all Lifelines are long enough
      ensureLifelineHeights();
    }
    return newlink;
  }
}

class MessageDraggingTool extends go.DraggingTool {
  /**
   * Override to add DraggingInfo for links
   *
   * @param parts - Array of parts to be dragged
   * @param options - Options for drag and drop
   *
   * @return { Array } Array of drag
   */
  computeEffectiveCollection(parts, options) {
    const result = super.computeEffectiveCollection(parts, options);
    // add a dummy Node so that the user can select only Links and move them all
    result.add(new go.Node(), new go.DraggingInfo(new go.Point()));
    parts.each((part) => {
      // Add a dragging info to the result.
      if (part instanceof go.Link) {
        result.add(part, new go.DraggingInfo(part.getPoint(0).copy()));
      }
    });
    return result;
  }

  /**
   * @return { boolean } True if the player may move
   */
  mayMove() {
    return !this.diagram.isReadOnly && this.diagram.allowMove;
  }

  /**
   * Move parts to new position
   *
   * @param parts - List of parts to move
   * @param offset - Offset to move by in degrees
   * @param check - Check if parts need to be
   */
  moveParts(parts, offset, check) {
    super.moveParts(parts, offset, check);
    const it = parts.iterator;
    // This method will iterate over all links in the graph.
    while (it.next()) {
      // This method is called when the link is dragged.
      if (it.key instanceof go.Link) {
        const link = it.key;
        const startY = it.value.point.y; // DraggingInfo.point.y
        //let y = startY + offset.y;  // determine new Y coordinate value for this link
        let y = Math.max(180, startY + offset.y);
        const cellY = this.gridSnapCellSize.height;
        y = Math.round(y / cellY) * cellY; // snap to multiple of gridSnapCellSize.height
        const t = Math.max(0, convertYToTime(y));
        link.diagram.model.set(link.data, "time", t);
        link.invalidateRoute();
      }
    }
  }
}

/**
 * Computes the height of lifeline.
 *
 * @param duration - Duration of the lifeline.
 *
 * @return { number } Height of the
 */
function computeLifelineHeight(duration) {
  return LinePrefix + duration * MessageSpacing + LineSuffix;
}

/**
 * Given an activity compute the location it is in
 *
 * @param act - the activity to be evaluated
 *
 * @return { Point } the location of
 */
function computeActivityLocation(act) {
  const groupdata = myDiagram.model.findNodeDataForKey(act.group);
  // Returns the point object for the groupdata.
  if (groupdata === null) return new go.Point();
  const grouploc = go.Point.parse(groupdata.loc);
  return new go.Point(grouploc.x, convertTimeToY(act.start) - ActivityStart);
}

/**
 * Compute the location of the activity
 *
 * @param loc - Object with x and y properties
 * @param act - Object with act property
 */
function backComputeActivityLocation(loc, act) {
  myDiagram.model.setDataProperty(
    act,
    "start",
    convertYToTime(loc.y + ActivityStart)
  );
}

/**
 * Computes the height of the activity
 *
 * @param duration - The duration of the activity in seconds
 *
 * @return { Number } The height
 */
function computeActivityHeight(duration) {
  return ActivityStart + duration * MessageSpacing + ActivityEnd;
}

/**
 * Computes the height of the back activity.
 *
 * @param height - Height of the back activity.
 *
 * @return { number } Height of the activity
 */
function backComputeActivityHeight(height) {
  return (height - ActivityStart - ActivityEnd) / MessageSpacing;
}

/**
 * Converts time to y - coordinate
 *
 * @param t - Time to convert in seconds
 *
 * @return { Number } Y - coordinate of
 */
function convertTimeToY(t) {
  return t * MessageSpacing + LinePrefix;
}

/**
 * Converts a y coordinate to a time.
 *
 * @param y - The y coordinate to convert.
 *
 * @return { number } The time
 */
function convertYToTime(y) {
  return (y - LinePrefix) / MessageSpacing;
}

/**
 * Ensures lifeline heights are maintained
 *
 * @param e - Event that triggered this
 */
function ensureLifelineHeights(e) {
  const arr = myDiagram.model.nodeDataArray;
  let max = -1;
  for (let i = 0; i < arr.length; i++) {
    const act = arr[i];
    if (max > 0) {
      if (!gr.isGroup) continue;
      if (max > gr.duration) {
        // esto solo se extiende, nunca se encoge
        myDiagram.model.setDataProperty(gr, "duration", max);
      }
    }
  }
}

// Obtiene el contenido del diagrama
const getDiagramContent = async () => {
  await axios
    .get(route("api.diagrams.get", props.diagram.id))
    .then((response) => {
      modelData = response.data.content;
    })
    .catch((error) => console.log(error));
};

const saveDiagramContent = async (inCache) => {
  if (modelData !== myDiagram.model.toJson() || !inCache) {
    modelData = myDiagram.model.toJson();
    updateDiagramContent(modelData, inCache);
    console.log(modelData);
  }
};

// Actualizar el diagrama
const updateDiagramContent = async (content, inCache) => {
  console.log("updateDiagramContent");
  axios
    .put(route("api.diagrams.update", props.diagram.id), {
      content: content,
      cache: inCache,
    })
    .then((response) => {
      console.log("updateDiagramContent");
    })
    .catch((error) => console.log(error));
};

const clearCanvas = () => {
  myDiagram.clear();
};

async function load() {
  await getDiagramContent();
  myDiagram.model = go.Model.fromJson(modelData);
}

/**
 * Undo the last action that was
 */
function undo() {
  myDiagram.commandHandler.undo();
}

/**
 * Redo a previously undone
 */
function redo() {
  myDiagram.commandHandler.redo();
}

/**
 * increase the zoom by 1
 */
function zoomIn() {
  myDiagram.commandHandler.increaseZoom();
  myDiagram.commandHandler.increaseZoom();
}

/**
 * decrease zoom by 2
 */
function zoomOut() {
  myDiagram.commandHandler.decreaseZoom();
  myDiagram.commandHandler.decreaseZoom();
}


/**
 * Callback for download operation.
 *
 * @param blob - URL of the blob to download.
 * @param extension - File extension to save to.
 *
 * @return { undefined } No return
 */
function myCallback(blob, extension) {
  var url = window.URL.createObjectURL(blob);
  var filename = "diagram " + props.diagram.name + extension;

  var a = document.createElement("a");
  a.style = "display: none";
  a.href = url;
  a.download = filename;

  if (window.navigator.msSaveBlob !== undefined) {
    window.navigator.msSaveBlob(blob, filename);
    return;
  }

  document.body.appendChild(a);
  requestAnimationFrame(() => {
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  });
}

// Exportar el diagrama a JSON
const exportAsJSON = () => {
  const jsonData = myDiagram.model.toJson();

  const blob = new Blob([jsonData], { type: "application/json" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = "diagrama.json";
  link.click();
};

const exportAsCode = async (language) => {
  let routeExport = "api.diagrams.exports";
  let extension = "";
  // Este método se utiliza para configurar la extensión de exportación de ruta
  switch (language) {
    case "java":
      routeExport = routeExport + ".java";
      extension = ".java";
      break;
    case "php":
      routeExport = routeExport + ".php";
      extension = ".php";
      break;
    case "cpp":
      routeExport = routeExport + ".cpp";
      extension = ".cpp";
      break;
    case "csharp":
      routeExport = routeExport + ".csharp";
      extension = ".cs";
      break;
    default:
      break;
  }

  await axios
    .get(route(routeExport, props.diagram.id))
    .then((response) => {
      const blob = new Blob([response.data], { type: "text/plain" });

      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");

      a.href = url;
      a.download = toPascalCase(props.diagram.name) + extension;

      a.click();

      URL.revokeObjectURL(url);
    })
    .catch((error) => console.log(error));
};

// Convierte una cadena a pascal
const toPascalCase = (text) => {
  const words = text.split(" ");
  const capitalized = words.map((word) => {
    return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
  });
  return capitalized.join("");
};

const exportAsXMI = async () => {
  await axios
    .get(route("api.diagrams.exports.xmi", props.diagram.id))
    .then((response) => {
      const texto = response.data;
      const blob = new Blob([texto], { type: "application/xmi+xml" });
      const url = URL.createObjectURL(blob);

      const enlace = document.createElement("a");
      enlace.download = "archivo.xmi";
      enlace.href = url;

      enlace.click();
      URL.revokeObjectURL(url);
    })
    .catch((error) => console.log(error));
};

// Conecte el socket para escuchar
const connectSocket = () => {
  try {
    window.Echo.join(`room.${props.diagram.id}`)
      .here((users) => {
        console.log("entraste");
        onlineUsers.value = users;
        console.log(onlineUsers.value);
      })
      .joining((user) => {
        console.log("alguien entró");
        onlineUsers.value.push(user);
        console.log(onlineUsers.value);
      })
      .leaving((user) => {
        console.log("alguien salió");
        onlineUsers.value.splice(onlineUsers.value.indexOf(user), 1);
      })
      .error((error) => {
        console.log(JSON.stringify(error));
      })
      .listen(".room.content", ({ content }) => {
        if (content.toString() != modelData.toString()) {
          console.log("modelo actualizado por evento");
          myDiagram.animationManager.isEnabled = false;
          modelData = content;
          myDiagram.model = go.Model.fromJson(content);
          myDiagram.animationManager.isEnabled = true;
        }
        /* this.userMessage(message, user); */
      });
  } catch (error) {
    print(error);
  }
};

// Crea e inicializa el editor de diagramas
const initEditor = async () => {
  myDiagram = new go.Diagram("goDiagramDiv", {
    allowCopy: false,
    linkingTool: $(MessagingTool),
    "resizingTool.isGridSnapEnabled": true,
    draggingTool: $(MessageDraggingTool),
    "draggingTool.gridSnapCellSize": new go.Size(1, MessageSpacing / 4),
    "draggingTool.isGridSnapEnabled": true,
    SelectionMoved: ensureLifelineHeights,
    PartResized: ensureLifelineHeights,
    "undoManager.isEnabled": true,
    initialAutoScale: go.Diagram.Uniform,
    initialContentAlignment: go.Spot.Center,
    initialPosition: new go.Point(500, -200),
    scrollsPageOnFocus: true,
  });

  //Línea de vida
  myDiagram.groupTemplate = $(
    go.Group,
    "Vertical",
    {
      locationSpot: go.Spot.Bottom,
      locationObjectName: "HEADER",
      minLocation: new go.Point(0, 0),
      maxLocation: new go.Point(9999, 0),
      selectionObjectName: "HEADER",
    },
    new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
      go.Point.stringify
    ),
    $(
      go.Panel,
      "Auto",
      { name: "HEADER" },
      $(go.Shape, "Rectangle", {
        fill: $(go.Brush, "Linear", {
          0: "#bbdefb",
          1: go.Brush.darkenBy("#bbdefb", 0.1),
        }),
        stroke: null,
      }),
      $(
        go.TextBlock,
        {
          margin: 5,
          font: "400 10pt Source Sans Pro, sans-serif",
        },
        new go.Binding("text", "text")
      )
    ),
    $(
      go.Shape,
      {
        figure: "LineV",
        fill: null,
        stroke: "gray",
        strokeDashArray: [3, 3],
        width: 1,
        alignment: go.Spot.Center,
        portId: "",
        fromLinkable: false,
        fromLinkableDuplicates: false,
        toLinkable: false,
        toLinkableDuplicates: false,
        cursor: "pointer",
      },
      new go.Binding("height", "duration", computeLifelineHeight)
    )
  );

  //Flechas
  myDiagram.linkTemplate = $(
    MessageLink,
    {
      selectionAdorned: true,
      curviness: 0,
    },
    $(
      go.Shape,
      "Rectangle",
      { stroke: "black" },
      new go.Binding("strokeDashArray", "isReturn", function (isReturn) {
        return isReturn ? [3, 3] : null;
      })
    ),

    $(
      go.Shape,
      { stroke: "black" },
      new go.Binding("toArrow", "synchronous", function (synchronous) {
        return synchronous == "yes" ? "Triangle" : "OpenTriangle";
      })
    ),
    $(
      go.TextBlock,
      {
        font: "400 9pt Source Sans Pro, sans-serif",
        segmentIndex: 0,
        segmentOffset: new go.Point(NaN, NaN),
        isMultiline: false,
        editable: true,
      },
      new go.Binding("text", "text").makeTwoWay(),
      new go.Binding("type", "text").makeTwoWay()
    )
  );

  myDiagram.nodeTemplate = $(
    go.Node,
    {
      locationSpot: go.Spot.Top,
      locationObjectName: "SHAPE",
      minLocation: new go.Point(NaN, LinePrefix - ActivityStart),
      maxLocation: new go.Point(NaN, 19999),
      selectionObjectName: "SHAPE",
      resizable: true,
      resizeObjectName: "SHAPE",
      resizeAdornmentTemplate: $(
        go.Adornment,
        "Spot",
        $(go.Placeholder),
        $(
          go.Shape, // only a bottom resize handle
          {
            alignment: go.Spot.Bottom,
            cursor: "col-resize",
            desiredSize: new go.Size(6, 6),
            fill: "yellow",
          }
        )
      ),
    },
    new go.Binding("location", "", computeActivityLocation).makeTwoWay(
      backComputeActivityLocation
    ),
    $(
      go.Shape,
      "Rectangle",
      {
        name: "SHAPE",
        fill: "white",
        stroke: "black",
        width: ActivityWidth,
        // permitir que las actividades se redimensionen a 1/4 de una unidad de tiempo
        minSize: new go.Size(ActivityWidth, computeActivityHeight(0.25)),
      },
      new go.Binding("height", "duration", computeActivityHeight).makeTwoWay(
        backComputeActivityHeight
      )
    )
  );

  myDiagram.addDiagramListener("LinkDrawn", function (e) {
    const link = e.subject;
    const fromNode = link.fromNode;
    const toNode = link.toNode;
    if (fromNode && toNode) {
      const fromX = fromNode.location.x;
      const toX = toNode.location.x;
      const isReturn = fromX > toX; // Comprueba la dirección de la conexión
      myDiagram.model.setDataProperty(link.data, "isReturn", isReturn);
    }
  });

  myDiagram.groupTemplateMap.add(
    "object",
    $(
      go.Group,
      "Vertical",
      {
        locationSpot: go.Spot.Bottom,
        locationObjectName: "HEADER",
        minLocation: new go.Point(-Infinity, 129.935),
        maxLocation: new go.Point(Infinity, 0),
        selectionObjectName: "HEADER",
        doubleClick: function (e, node) {
          // Habilita la edición de texto al hacer doble clic en el encabezado
          var textBlock = node.findObject("TEXTBLOCK");
          if (textBlock) {
            myDiagram.commandHandler.editTextBlock(textBlock);
          }
        },
      },
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
        go.Point.stringify
      ),
      $(
        go.Panel,
        "Auto",
        { name: "HEADER" },
        $(go.Shape, "Rectangle", {
          fill: "white",
          stroke: "black",
          strokeWidth: 1.5,
          width: 130.1,
          height: 65,
        }),
        $(
          go.TextBlock,
          {
            margin: 0,
            font: "400 10pt Source Sans Pro, sans-serif",
            stroke: "black",
            editable: true,
            textEdited: function (textBlock, oldText, newText) {
              var groupData = textBlock.part.data;
              myDiagram.model.setDataProperty(groupData, "text", newText);
            },
          },
          new go.Binding("text", "text")
        )
      ),

      $(
        go.Shape,
        lineV,
        new go.Binding("height", "duration", (duration) => {
          return LinePrefix + duration * MessageSpacing + LineSuffix;
        })
      )
    )
  );

  myDiagram.groupTemplateMap.add(
    "actor",
    $(
      go.Group,
      "Vertical",
      {
        locationSpot: go.Spot.Bottom,
        locationObjectName: "HEADER",
        minLocation: new go.Point(-Infinity, 129.935),
        maxLocation: new go.Point(Infinity, 0),
        selectionObjectName: "HEADER",
        doubleClick: function (e, node) {
          // Habilita la edición de texto al hacer doble clic en el encabezado
          var textBlock = node.findObject("TEXTBLOCK");
          if (textBlock) {
            myDiagram.commandHandler.editTextBlock(textBlock);
          }
        },
      },
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
        go.Point.stringify
      ),
      $(
        go.Panel,
        "Auto",
        { name: "HEADER" },
        $(go.Picture, { source: "../images/actor.png", width: 30, height: 55 })
      ),
      $(
        go.TextBlock,
        {
          font: "400 10pt Source Sans Pro, sans-serif",
          stroke: "black",
          editable: true,
          textEdited: function (textBlock, oldText, newText) {
            var groupData = textBlock.part.data;
            myDiagram.model.setDataProperty(groupData, "text", newText);
          },
        },
        new go.Binding("text", "text")
      ),
      $(
        go.Shape,
        lineV,
        new go.Binding("height", "duration", computeLifelineHeight)
      )
    )
  );

  myDiagram.groupTemplateMap.add(
    "entity",
    $(
      go.Group,
      "Vertical",
      {
        locationSpot: go.Spot.Bottom,
        locationObjectName: "HEADER",
        minLocation: new go.Point(-Infinity, 129.935),
        maxLocation: new go.Point(Infinity, 0),
        selectionObjectName: "HEADER",
        doubleClick: function (e, node) {
          // Habilita la edición de texto al hacer doble clic en el encabezado
          var textBlock = node.findObject("TEXTBLOCK");
          if (textBlock) {
            myDiagram.commandHandler.editTextBlock(textBlock);
          }
        },
      },
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
        go.Point.stringify
      ),
      $(
        go.Panel,
        "Auto",
        { name: "HEADER" },
        $(go.Picture, { source: "../images/entity.png", width: 65, height: 65 })
      ),
      $(
        go.TextBlock,
        {
          font: "400 10pt Source Sans Pro, sans-serif",
          stroke: "black",
          editable: true,
          textEdited: function (textBlock, oldText, newText) {
            var groupData = textBlock.part.data;
            myDiagram.model.setDataProperty(groupData, "text", newText);
          },
        },
        new go.Binding("text", "text")
      ),
      $(
        go.Shape,
        lineV,
        new go.Binding("height", "duration", (duration) => {
          return LinePrefix + duration * MessageSpacing + LineSuffix - 10;
        })
      )
    )
  );

  myDiagram.groupTemplateMap.add(
    "boundary",
    $(
      go.Group,
      "Vertical",
      {
        locationSpot: go.Spot.Bottom,
        locationObjectName: "HEADER",
        minLocation: new go.Point(-Infinity, 129.935),
        maxLocation: new go.Point(Infinity, 0),
        selectionObjectName: "HEADER",
        doubleClick: function (e, node) {
          // Habilita la edición de texto al hacer doble clic en el encabezado
          var textBlock = node.findObject("TEXTBLOCK");
          if (textBlock) {
            myDiagram.commandHandler.editTextBlock(textBlock);
          }
        },
      },
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
        go.Point.stringify
      ),
      $(
        go.Panel,
        "Auto",
        { name: "HEADER" },
        $(go.Picture, {
          source: "../images/boundary.png",
          width: 77,
          height: 65,
          margin: new go.Margin(0, 15, 0, 0),
        })
      ),
      $(
        go.TextBlock,
        {
          font: "400 10pt Source Sans Pro, sans-serif",
          stroke: "black",
          editable: true,
          textEdited: function (textBlock, oldText, newText) {
            var groupData = textBlock.part.data;
            myDiagram.model.setDataProperty(groupData, "text", newText);
          },
        },
        new go.Binding("text", "text")
      ),
      $(
        go.Shape,
        lineV,
        new go.Binding("height", "duration", (duration) => {
          return LinePrefix + duration * MessageSpacing + LineSuffix - 10;
        })
      )
    )
  );

  myDiagram.groupTemplateMap.add(
    "control",
    $(
      go.Group,
      "Vertical",
      {
        locationSpot: go.Spot.Bottom,
        locationObjectName: "HEADER",
        minLocation: new go.Point(-Infinity, 129.935),
        maxLocation: new go.Point(Infinity, 0),
        selectionObjectName: "HEADER",
        doubleClick: function (e, node) {
          // Habilita la edición de texto al hacer doble clic en el encabezado
          var textBlock = node.findObject("TEXTBLOCK");
          if (textBlock) {
            myDiagram.commandHandler.editTextBlock(textBlock);
          }
        },
      },
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
        go.Point.stringify
      ),
      $(
        go.Panel,
        "Auto",
        { name: "HEADER" },
        $(go.Picture, {
          source: "../images/control.png",
          width: 65,
          height: 75,
        })
      ),
      $(
        go.TextBlock,
        {
          font: "400 10pt Source Sans Pro, sans-serif",
          stroke: "black",
          editable: true,
          textEdited: function (textBlock, oldText, newText) {
            var groupData = textBlock.part.data;
            myDiagram.model.setDataProperty(groupData, "text", newText);
          },
        },
        new go.Binding("text", "text")
      ),
      $(
        go.Shape,
        lineV,
        new go.Binding("height", "duration", (duration) => {
          return LinePrefix + duration * MessageSpacing + LineSuffix - 10;
        })
      )
    )
  );
  //Fragmento LOOP
  myDiagram.groupTemplateMap.add(
    "loop",
    $(
      go.Group,
      "Auto",
      {
        resizable: true, // Permite redimensionar el grupo
        resizeObjectName: "SHAPE", // El objeto que se redimensiona es el rectángulo principal
        minLocation: new go.Point(-Infinity, 140),
      },
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
        go.Point.stringify
      ),
      $(
        go.Shape,
        "Rectangle", // Rectángulo principal
        { name: "SHAPE", fill: "transparent", width: 250, height: 150 },
        new go.Binding("width", "width").makeTwoWay(),
        new go.Binding("height", "height").makeTwoWay()
      ),
      $(go.TextBlock, {
        alignment: go.Spot.TopLeft,
        font: "bold 11pt sans-serif",
        margin: new go.Margin(5, 0, 5, 10),
        editable: false,
        text: "loop",
      }),
      $(
        go.TextBlock,
        {
          alignment: go.Spot.TopCenter,
          font: "11pt sans-serif",
          margin: new go.Margin(5, 0, 5, -50),
          editable: true,
        },
        new go.Binding("text", "text").makeTwoWay()
      )
    )
  );

  //Fragmento ALT
  myDiagram.groupTemplateMap.add(
    "alt",
    $(
      go.Group,
      "Vertical",
      {
        resizable: true,
        resizeObjectName: "SHAPE",
        minLocation: new go.Point(-Infinity, 140),
      },
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
        go.Point.stringify
      ),
      $(
        go.Panel,
        "Auto",

        $(
          go.Shape,
          "Rectangle", // Rectángulo principal
          { name: "SHAPE", fill: "transparent", width: 250, height: 150 },
          new go.Binding("width", "width").makeTwoWay(),
          new go.Binding("height", "height").makeTwoWay()
        ),
        $(
          go.TextBlock, // la etiqueta del elemento opt
          {
            alignment: go.Spot.TopLeft,
            font: "bold 11pt sans-serif",
            margin: new go.Margin(5, 0, 5, 10),
            editable: false,
            text: "alt",
          }
        ),
        $(
          go.Panel,
          "Auto", // Panel para la línea punteada
          {
            height: 2,
            width: 250,
          },
          $(go.Shape, "LineH", {
            stroke: "black",
            strokeWidth: 1,
            strokeDashArray: [4, 2],
          }),

          new go.Binding("width", "width")
        ),
        $(
          go.TextBlock, // la etiqueta del elemento opt
          {
            alignment: go.Spot.TopLeft,
            font: "11pt sans-serif",
            margin: new go.Margin(20, 0, 5, 10),
            editable: true,
          },
          new go.Binding("text", "text").makeTwoWay()
        ),
        $(
          go.TextBlock,
          {
            alignment: go.Spot.LeftCenter,
            font: "11pt sans-serif",
            margin: new go.Margin(25, 0, 0, 10),
            editable: true,
          },
          new go.Binding("text", "text2").makeTwoWay()
        )
      )
    )
  );

  //Fragmento OPT
  myDiagram.groupTemplateMap.add(
    "opt",
    $(
      go.Group,
      "Auto",
      {
        resizable: true, // Permite redimensionar el grupo
        resizeObjectName: "SHAPE", // El objeto que se redimensiona es el rectángulo principal
        minLocation: new go.Point(-Infinity, 140),
      },
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(
        go.Point.stringify
      ),
      $(
        go.Shape,
        "Rectangle", // Rectángulo principal
        { name: "SHAPE", fill: "transparent" },
        new go.Binding("width", "width").makeTwoWay(),
        new go.Binding("height", "height").makeTwoWay()
      ),
      $(
        go.TextBlock, // la etiqueta del elemento opt
        {
          alignment: go.Spot.TopLeft,
          font: "bold 11pt sans-serif",
          margin: new go.Margin(5, 0, 5, 10),
          editable: false,
          text: "opt",
        }
      ),
      $(
        go.TextBlock,
        {
          alignment: go.Spot.TopCenter,
          font: "11pt sans-serif",
          margin: new go.Margin(5, 0, 5, -60),
          editable: true,
        },
        new go.Binding("text", "text").makeTwoWay()
      )
    )
  );

  myDiagram.model = $(go.GraphLinksModel, {
    linkFromPortIdProperty: "fromPort",
    linkToPortIdProperty: "toPort",
    linkKeyProperty: "key", // Propiedad clave del enlace
  });

  await getDiagramContent();

  if (modelData) {
    myDiagram.model = go.Model.fromJson(modelData);
    if (myDiagram.model.nodeDataArray.toString()) {
      myDiagram.contentAlignment = go.Spot.Center;
    }
  }

  myDiagram.addModelChangedListener(onModelChanged);

  myDiagram.addDiagramListener("ExternalObjectsDropped", function (e) {
    var diagram = e.diagram;
    var droppedParts = e.subject;

    droppedParts.each(function (part) {
      if (
        part.category === "alt" ||
        part.category === "opt" ||
        part.category === "loop"
      ) {
        var relatedElements = part.relatedElements ?? [];

        var horizontalPadding = 30;
        var bounds = part.actualBounds.copy();
        var centerX = part.actualBounds.centerX;

        bounds.width -= horizontalPadding * 2;
        bounds.setPoint(new go.Point(centerX - bounds.width / 2, bounds.y));

        var collidingElements = diagram.findObjectsIn(bounds, function (obj) {
          if ("from" in obj.part.data) {
            return obj.part.data.category !== "alt" &&
              obj.part.data.category !== "opt" &&
              obj.part.data.category !== "loop"
              ? obj.part.data
              : null;
          }
        });

        var relatedElements = [];
        collidingElements.each(function (collidingElement) {
          if (collidingElement) {
            relatedElements.push({
              key: collidingElement.key,
              time: collidingElement.time,
            });
          }
        });

        if (relatedElements) {
          diagram.model.setDataProperty(
            part.data,
            "relatedElements",
            relatedElements
          );
        }
      }
    });
  });

  /**
   * Move parts to the centre of the diagram
   *
   * @param e - event to check moved parts
   *
   * @return { Array } Array of
   */
  myDiagram.addDiagramListener("SelectionMoved", function (e) {
    var diagram = e.diagram;
    var movedParts = e.subject;

    /**
     * Checks if a part collides with any other part in the diagram
     *
     * @param part - Part to be checked for collisions
     *
     * @return { Object } Part with related
     */
    movedParts.each(function (part) {
      // This method is called when the part is a alt, opt, loop or alt fragment.
      if (
        part.category === "alt" ||
        part.category === "opt" ||
        part.category === "loop"
      ) {
        var horizontalPadding = 30;
        var bounds = part.actualBounds.copy();
        var centerX = part.actualBounds.centerX;

        bounds.width -= horizontalPadding * 2;
        bounds.setPoint(new go.Point(centerX - bounds.width / 2, bounds.y));

        var collidingElements = diagram.findObjectsIn(bounds, function (obj) {
          // Returns the data from the part of the object.
          if ("from" in obj.part.data) {
            return obj.part.data.type != "fragment" ? obj.part.data : null;
          }
        });

        var relatedElements = [];
        /**
         * @param collidingElement
         */
        collidingElements.each(function (collidingElement) {
          // Add a related element to the relatedElements array.
          if (collidingElement) {
            relatedElements.push({
              key: collidingElement.key,
              time: collidingElement.time,
            });
          }
        });

        // Set the relatedElements of the part.
        if (relatedElements != part.relatedElements) {
          diagram.model.setDataProperty(
            part.data,
            "relatedElements",
            relatedElements
          );
        }
      }
    });
  });

  myDiagram.grid = $(
    go.Panel,
    "Grid",
    $(go.Shape, "LineH", {
      strokeWidth: 0.5,
      strokeDashArray: [0, 9.5, 0.5, 0],
    })
  );

  /**
   * Saves diagram content when model changes.
   *
   * @param go.ChangedEvent e - Event with isTransactionFinished property
   */
  function onModelChanged(e) {
    if (e.isTransactionFinished) {
      saveDiagramContent(true);
    }
  }
};

const initPalette = () => {
  const myPalette = $(go.Palette, "myPaletteDiv", {
    scrollsPageOnFocus: false,
    nodeTemplateMap: myDiagram.nodeTemplateMap,
    groupTemplateMap: myDiagram.groupTemplateMap,
    initialScale: 0.75,
    //initialPosition: new go.Point(500, 400),
    padding: new go.Margin(100, 0, 0, 0),
    layout: $(go.GridLayout, {
      wrappingColumn: 3,
      alignment: go.GridLayout.Position,
      cellSize: new go.Size(50, 50),
    }),
  });

  myPalette.model = new go.GraphLinksModel([
    {
      category: "actor",
      key: "actor",
      text: "Actor",
      isGroup: true,
      duration: 10,
    },
    {
      category: "object",
      key: "object",
      text: "Object",
      isGroup: true,
      duration: 10,
    },
    {
      category: "entity",
      key: "entity",
      text: "Entity",
      isGroup: true,
      duration: 10,
    },
    {
      category: "boundary",
      key: "boundary",
      text: "Boundary",
      isGroup: true,
      duration: 10,
    },
    {
      category: "control",
      key: "control",
      text: "Control",
      isGroup: true,
      duration: 10,
    },
    {
      category: "alt",
      key: "alt",
      text: "[condition]",
      text2: "[condition]",
      isGroup: true,
      relatedElements: [],
      width: 250,
      height: 150,
    },
    {
      category: "opt",
      key: "opt",
      text: "[condition]",
      isGroup: true,
      relatedElements: [],
      width: 250,
      height: 150,
    },
    {
      category: "loop",
      key: "loop",
      text: "[condition]",
      isGroup: true,
      relatedElements: [],
      width: 250,
      height: 150,
    },
  ]);
};

defineExpose({
  exportAsXMI,
  exportAsCode,
  saveDiagramContent,
});
</script>

<style>
@import "@/Assets/styles/secuence-diagram.css";
</style>
