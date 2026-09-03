#include "../include/BTreeNode.h"
#include <iostream>

BTreeNode::BTreeNode(int order, bool leaf)
    : isLeaf(leaf), order(order) {}

BTreeNode::~BTreeNode() {
    for (auto child : children) {
        delete child;
    }
}

void BTreeNode::traverse() const {
    int i;
    for (i = 0; i < (int)keys.size(); i++) {
        if (!isLeaf) children[i]->traverse();
        std::cout << "  [" << keys[i].value << ": " << rows[i].data << "]\n";
    }
    if (!isLeaf) children[i]->traverse();
}

BTreeNode* BTreeNode::search(const Key& key) {
    int i = 0;
    while (i < (int)keys.size() && key > keys[i])
        i++;
    if (i < (int)keys.size() && key == keys[i])
        return this;
    if (isLeaf)
        return nullptr;
    return children[i]->search(key);
}

void BTreeNode::insertNonFull(const Key& key, const Row& row) {
    int i = (int)keys.size() - 1;
    if (isLeaf) {
        keys.resize(keys.size() + 1);
        rows.resize(rows.size() + 1);
        while (i >= 0 && key < keys[i]) {
            keys[i + 1] = keys[i];
            rows[i + 1] = rows[i];
            i--;
        }
        keys[i + 1] = key;
        rows[i + 1] = row;
    } else {
        while (i >= 0 && key < keys[i])
            i--;
        i++;
        if ((int)children[i]->keys.size() == 2 * order - 1) {
            splitChild(i, children[i]);
            if (key > keys[i])
                i++;
        }
        children[i]->insertNonFull(key, row);
    }
}

void BTreeNode::splitChild(int i, BTreeNode* child) {
    BTreeNode* newNode = new BTreeNode(child->order, child->isLeaf);
    newNode->keys.resize(order - 1);
    newNode->rows.resize(order - 1);
    for (int j = 0; j < order - 1; j++) {
        newNode->keys[j] = child->keys[j + order];
        newNode->rows[j] = child->rows[j + order];
    }
    if (!child->isLeaf) {
        newNode->children.resize(order);
        for (int j = 0; j < order; j++)
            newNode->children[j] = child->children[j + order];
    }
    child->keys.resize(order - 1);
    child->rows.resize(order - 1);
    if (!child->isLeaf)
        child->children.resize(order);
    children.insert(children.begin() + i + 1, newNode);
    keys.insert(keys.begin() + i, child->keys[order - 1]);
    rows.insert(rows.begin() + i, child->rows[order - 1]);
    child->keys.resize(order - 1);
    child->rows.resize(order - 1);
}

int BTreeNode::findKey(const Key& key) {
    int idx = 0;
    while (idx < (int)keys.size() && keys[idx] < key)
        idx++;
    return idx;
}

void BTreeNode::removeFromLeaf(int idx) {
    keys.erase(keys.begin() + idx);
    rows.erase(rows.begin() + idx);
}

void BTreeNode::removeFromNonLeaf(int idx) {
    Key k = keys[idx];
    if ((int)children[idx]->keys.size() >= order) {
        Key pred = getPredecessor(idx);
        Row predRow = children[idx]->rows.back();
        keys[idx] = pred;
        rows[idx] = predRow;
        children[idx]->remove(pred);
    } else if ((int)children[idx + 1]->keys.size() >= order) {
        Key succ = getSuccessor(idx);
        Row succRow = children[idx + 1]->rows[0];
        keys[idx] = succ;
        rows[idx] = succRow;
        children[idx + 1]->remove(succ);
    } else {
        merge(idx);
        children[idx]->remove(k);
    }
}

Key BTreeNode::getPredecessor(int idx) {
    BTreeNode* cur = children[idx];
    while (!cur->isLeaf)
        cur = cur->children[cur->keys.size()];
    return cur->keys[cur->keys.size() - 1];
}

Key BTreeNode::getSuccessor(int idx) {
    BTreeNode* cur = children[idx + 1];
    while (!cur->isLeaf)
        cur = cur->children[0];
    return cur->keys[0];
}

void BTreeNode::fill(int idx) {
    if (idx != 0 && (int)children[idx - 1]->keys.size() >= order)
        borrowFromPrev(idx);
    else if (idx != (int)children.size() - 1 && (int)children[idx + 1]->keys.size() >= order)
        borrowFromNext(idx);
    else {
        if (idx != (int)children.size() - 1)
            merge(idx);
        else
            merge(idx - 1);
    }
}

void BTreeNode::borrowFromPrev(int idx) {
    BTreeNode* child = children[idx];
    BTreeNode* sibling = children[idx - 1];
    child->keys.insert(child->keys.begin(), keys[idx - 1]);
    child->rows.insert(child->rows.begin(), rows[idx - 1]);
    if (!child->isLeaf)
        child->children.insert(child->children.begin(), sibling->children.back());
    keys[idx - 1] = sibling->keys.back();
    rows[idx - 1] = sibling->rows.back();
    sibling->keys.pop_back();
    sibling->rows.pop_back();
    if (!sibling->isLeaf)
        sibling->children.pop_back();
}

void BTreeNode::borrowFromNext(int idx) {
    BTreeNode* child = children[idx];
    BTreeNode* sibling = children[idx + 1];
    child->keys.push_back(keys[idx]);
    child->rows.push_back(rows[idx]);
    if (!child->isLeaf)
        child->children.push_back(sibling->children[0]);
    keys[idx] = sibling->keys[0];
    rows[idx] = sibling->rows[0];
    sibling->keys.erase(sibling->keys.begin());
    sibling->rows.erase(sibling->rows.begin());
    if (!sibling->isLeaf)
        sibling->children.erase(sibling->children.begin());
}

void BTreeNode::merge(int idx) {
    BTreeNode* child = children[idx];
    BTreeNode* sibling = children[idx + 1];
    child->keys.push_back(keys[idx]);
    child->rows.push_back(rows[idx]);
    for (int j = 0; j < (int)sibling->keys.size(); j++) {
        child->keys.push_back(sibling->keys[j]);
        child->rows.push_back(sibling->rows[j]);
    }
    if (!child->isLeaf) {
        for (int j = 0; j <= (int)sibling->keys.size(); j++)
            child->children.push_back(sibling->children[j]);
    }
    keys.erase(keys.begin() + idx);
    rows.erase(rows.begin() + idx);
    children.erase(children.begin() + idx + 1);
    sibling->keys.clear();
    sibling->rows.clear();
    sibling->children.clear();
    delete sibling;
}

bool BTreeNode::remove(const Key& key) {
    int idx = findKey(key);
    if (idx < (int)keys.size() && keys[idx] == key) {
        if (isLeaf)
            removeFromLeaf(idx);
        else
            removeFromNonLeaf(idx);
        return true;
    }
    if (isLeaf)
        return false;
    bool last = (idx == (int)keys.size());
    if ((int)children[idx]->keys.size() < order)
        fill(idx);
    if (last && idx > (int)keys.size())
        return children[idx - 1]->remove(key);
    else
        return children[idx]->remove(key);
}
