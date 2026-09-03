#include <iostream>
#include <chrono>
#include <cstdlib>
#include <algorithm>
#include <sstream>
#include "../include/QueryPlanner.h"
#include "../include/HeapEngine.h"
#include "../include/TreeEngine.h"

using namespace std::chrono;

const int NUM_INSERT = 10000;
const int NUM_SEARCH = 5000;

long long benchmarkInsert(StorageEngine& eng, int count) {
    auto start = high_resolution_clock::now();
    for (int i = 0; i < count; i++) {
        int keyVal = rand() % 1000000;
        eng.insert(Key(keyVal), Row(keyVal, "value_" + std::to_string(keyVal)));
    }
    auto end = high_resolution_clock::now();
    return duration_cast<microseconds>(end - start).count();
}

long long benchmarkFind(StorageEngine& eng, const std::vector<int>& keys) {
    auto start = high_resolution_clock::now();
    int found = 0;
    for (int k : keys) {
        if (eng.find(Key(k))) found++;
    }
    auto end = high_resolution_clock::now();
    std::cout << "    Found: " << found << "/" << keys.size() << "\n";
    return duration_cast<microseconds>(end - start).count();
}

long long benchmarkRemove(StorageEngine& eng, const std::vector<int>& keys) {
    auto start = high_resolution_clock::now();
    int removed = 0;
    for (int k : keys) {
        if (eng.remove(Key(k))) removed++;
    }
    auto end = high_resolution_clock::now();
    std::cout << "    Removed: " << removed << "/" << keys.size() << "\n";
    return duration_cast<microseconds>(end - start).count();
}

void runBenchmark(const std::string& label, StorageEngine& eng) {
    std::cout << "\n=== " << label << " ===\n";

    long long tIns = benchmarkInsert(eng, NUM_INSERT);
    std::cout << "  Insert " << NUM_INSERT << " rows: " << tIns << " us  ("
              << (tIns / NUM_INSERT) << " us/op)\n";

    std::vector<int> searchKeys;
    for (int i = 0; i < NUM_SEARCH; i++)
        searchKeys.push_back(rand() % 1000000);

    long long tFind = benchmarkFind(eng, searchKeys);
    std::cout << "  Search " << NUM_SEARCH << " keys: " << tFind << " us  ("
              << (tFind / NUM_SEARCH) << " us/op)\n";

    long long tRem = benchmarkRemove(eng, searchKeys);
    std::cout << "  Remove " << NUM_SEARCH << " keys: " << tRem << " us  ("
              << (tRem / NUM_SEARCH) << " us/op)\n";
}

int main() {
    srand(42);

    std::cout << "MINI DATABASE ENGINE WITH B-TREE INDEX\n";
    std::cout << "=======================================\n";

    QueryPlanner planner;
    TreeEngine tree(4);
    planner.setEngine(&tree);

    std::cout << "\nType 'help' for commands.\n";

    std::string line;
    while (true) {
        std::cout << "> ";
        if (!std::getline(std::cin, line)) {
            break;
        }
        if (line.empty()) {
            continue;
        }

        std::istringstream iss(line);
        std::string cmd;
        iss >> cmd;

        if (cmd == "help") {
            std::cout << "Commands:\n";
            std::cout << "  insert <key> <value>\n";
            std::cout << "  find <key>\n";
            std::cout << "  remove <key>\n";
            std::cout << "  benchmark\n";
            std::cout << "  quit\n";
        } else if (cmd == "insert") {
            int key;
            std::string value;
            if (iss >> key) {
                std::getline(iss, value);
                if (!value.empty() && value[0] == ' ') {
                    value.erase(0, 1);
                }
                planner.executeInsert(Key(key), Row(key, value));
                std::cout << "Inserted key " << key << " with value '" << value << "'\n";
            } else {
                std::cout << "Usage: insert <key> <value>\n";
            }
        } else if (cmd == "find") {
            int key;
            if (iss >> key) {
                Row* row = planner.executeFind(Key(key));
                if (row) {
                    std::cout << "Found key " << key << " -> " << row->data << "\n";
                } else {
                    std::cout << "Not found\n";
                }
            } else {
                std::cout << "Usage: find <key>\n";
            }
        } else if (cmd == "remove") {
            int key;
            if (iss >> key) {
                bool removed = planner.executeRemove(Key(key));
                std::cout << (removed ? "Removed" : "Not found") << " key " << key << "\n";
            } else {
                std::cout << "Usage: remove <key>\n";
            }
        } else if (cmd == "benchmark") {
            runBenchmark("TreeEngine (B-Tree Index)", tree);
        } else if (cmd == "quit" || cmd == "exit") {
            break;
        } else {
            std::cout << "Unknown command. Type 'help'.\n";
        }
    }

    return 0;
}
