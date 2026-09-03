#ifndef BTREENODE_H
#define BTREENODE_H

#include "Key.h"
#include "Row.h"
#include <vector>

class BTreeNode {
public:
    bool isLeaf;
    std::vector<Key> keys;
    std::vector<Row> rows;
    std::vector<BTreeNode*> children;
    int order;

    BTreeNode(int order, bool leaf);
    ~BTreeNode();

    void insertNonFull(const Key& key, const Row& row);
    void splitChild(int i, BTreeNode* child);
    BTreeNode* search(const Key& key);
    bool remove(const Key& key);
    int findKey(const Key& key);
    void removeFromLeaf(int idx);
    void removeFromNonLeaf(int idx);
    Key getPredecessor(int idx);
    Key getSuccessor(int idx);
    void fill(int idx);
    void borrowFromPrev(int idx);
    void borrowFromNext(int idx);
    void merge(int idx);
    void traverse() const;
};

#endif
