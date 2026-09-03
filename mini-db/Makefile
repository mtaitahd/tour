CXX      = g++
CXXFLAGS = -std=c++17 -Wall -O2 -I include
SRC      = src/BTreeNode.cpp src/BTree.cpp src/HeapEngine.cpp \
           src/TreeEngine.cpp src/QueryPlanner.cpp src/main.cpp
OBJ      = $(SRC:.cpp=.o)
TARGET   = mini_db

all: $(TARGET)

$(TARGET): $(OBJ)
	$(CXX) $(CXXFLAGS) -o $@ $^

src/%.o: src/%.cpp
	$(CXX) $(CXXFLAGS) -c -o $@ $<

clean:
	del /Q src\*.o $(TARGET).exe 2>nul || true

run: $(TARGET)
	$(TARGET).exe

.PHONY: all clean run
