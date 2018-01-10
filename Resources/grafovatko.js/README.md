Grafovatko
==========

Grafovatko is a graph visualization software — from a graph specification (a list
of nodes and edges) renders a beautiful SVG diagram.

- **[Examples / Demo](example/)**
- [Project repository](https://git.frozen-doe.net/smalldb/grafovatko)

Features
--------

- JSON input, SVG output.
- JavaScript implementation — works in both command-line and web browser.
- Easy to extend with custom layout algorithms and/or shapes.


Installation
------------

From pre-built package (master branch only):

1. Download [grafovatko.tar.gz](dist/grafovatko.tar.gz) or [grafovatko.zip](dist/grafovatko.zip).
2. Unpack the archive into your project.
3. Include `grafovatko.min.js` in your web page.
4. Use `G` object to access Grafovatko library (i.e. `let gv = new G.GraphView()`).

From source:

1. Clone Git repository.
2. Run `make dist`
3. Copy `dist/grafovatko.min.js` to your project
4. Include `grafovatko.min.js` in your web page.
5. Use `G` object to access Grafovatko library (i.e. `let gv = new G.GraphView()`).


Usage
-----

Put an `<svg>` element into an HTML document:

```html
<!doctype html>
<meta charset=utf-8>
<title>Example</title>
<svg></svg>
```

### Manual layout

Create GGraphView, initialize it with a dataset:

```js
let graph_data = {
	nodes: [
		{ id: 'a', x:  50, y: 50, shape: 'circle' },
		{ id: 'b', x: 150, y: 50, shape: 'circle' }
	],
	edges: [
		{ id: 'e', start: 'a', end: 'b' }
	]
};
let gv = new G.GraphView('svg#svg1', graph_data);
```

Or create GGraphView and then create graph:

```js
let gv = new G.GraphView('svg#svg2');
let g = gv.graph;
let a = g.addNode('a', { x:  50, y: 50, shape: 'circle' });
let b = g.addNode('b', { x: 150, y: 50, shape: 'circle' });
let e = g.addEdge('e', {}, a, 'b');
```

Both examples will produce a little `a → b` diagram:

![(a)→(b)](example/example-hello_world.svg)


### Automatic layout

Simply specify layout name and its options and drop node coordinates:

```js
let graph_data = {
	nodes: [
		{ id: 'a', shape: 'circle' },
		{ id: 'b', shape: 'circle' }
	],
	edges: [
		{ id: 'e', start: 'a', end: 'b' }
	],
	graph: {
		layout: 'dagre'
	},
	layout: {
		rankdir: 'LR'
	}
};
let gv = new G.GraphView('svg#svg3', graph_data);
```

Or:

```js
let gv = new G.GraphView('svg#svg4');
let g = gv.graph;
g.setLayout('dagre', { rankdir: 'LR' });
let a = g.addNode('a', { shape: 'circle' });
let b = g.addNode('b', { shape: 'circle' });
let e = g.addEdge('e', {}, a, 'b');
```

The result is … as expected:

![(a)→(b)](example/example-hello_world.svg)


The Algorithm
-------------

1. Load graph — create GGraph, GNode, and GEdge objects.
2. Dereference node and edge names to objects.
3. Dereference shapes — Create GShape objects via GShapeLibrary, assign them to GNodes. One GShape instance renders many GNodes.
4. Calculate layout. — Assign positions to GNodes.
5. Render nodes — GNode uses GShape to do the rendering.
6. Render edges — GEdge renders on its own (?).


License
-------

The most of the code is published under Apache 2.0 license. See
[LICENSE](LICENSE) file for details.

