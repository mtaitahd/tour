# Mini Database Engine with B-Tree Index

## Project Structure

```
mini-db/
├── include/
│   ├── Row.h              # Data row (id + string)
│   ├── Key.h              # Key wrapper with comparison operators
│   ├── BTreeNode.h        # B-Tree node (keys, rows, children)
│   ├── BTree.h            # B-Tree data structure
│   ├── StorageEngine.h    # Abstract base class (polymorphism)
│   ├── HeapEngine.h       # Linear scan storage (inherits StorageEngine)
│   ├── TreeEngine.h       # B-Tree indexed storage (inherits StorageEngine)
│   └── QueryPlanner.h     # Strategy pattern: engine swap
├── src/
│   ├── main.cpp           # Driver with performance benchmark
│   ├── BTreeNode.cpp
│   ├── BTree.cpp
│   ├── HeapEngine.cpp
│   ├── TreeEngine.cpp
│   └── QueryPlanner.cpp
├── docs/
│   └── uml.puml           # PlantUML class diagram
├── Makefile
└── REPORT.md
```

## OOP Concepts Applied

### 1. Encapsulation
- `BTreeNode` and `BTree` hide internal node structure (keys, children pointers, splitting/merging logic).
- All B-Tree internals are private/protected; users interact only through `insert()`, `search()`, `remove()`.

### 2. Inheritance
- `StorageEngine` (abstract base) → `HeapEngine` and `TreeEngine`.
- Both derived classes implement the same interface with completely different storage strategies.

### 3. Polymorphism
- `StorageEngine::insert()`, `find()`, `remove()` are virtual functions.
- Calls through base-class pointer dispatch to the correct engine at runtime.

### 4. Strategy Pattern
- `QueryPlanner` holds a `StorageEngine*` and delegates all operations to it.
- Call `setEngine()` at runtime to swap between HeapEngine and TreeEngine without changing client code.

## Performance Benchmark

| Operation       | HeapEngine (O(n)) | TreeEngine (O(log n)) | Speedup |
|----------------|-------------------|----------------------|---------|
| Insert 10,000  | 2,214 µs         | 5,519 µs             | —       |
| Search 5,000   | 57,219 µs        | 905 µs               | **63x** |
| Remove 5,000   | 146,461 µs       | 1,376 µs             | **106x**|

Insert is faster in HeapEngine because push_back is O(1) amortized, while B-Tree insertion requires node splits. However, search and removal show dramatic speedups due to O(log n) B-Tree traversal vs O(n) linear scan.

## How to Build & Run

```bash
cd mini-db
make          # build
make run      # run benchmark
```

Or directly:

```bash
g++ -std=c++17 -Wall -O2 -I include -o mini_db.exe src/*.cpp
./mini_db.exe
```

## UML Diagram

Generate with PlantUML from `docs/uml.puml`.
