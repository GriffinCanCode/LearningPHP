#!/bin/bash

# NewsAggregator Development Servers Stop Script
# This script stops both backend and frontend servers

echo "🛑 Stopping NewsAggregator Development Servers..."
echo "================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to kill process on port
kill_port() {
    local port=$1
    local name=$2
    local pid=$(lsof -ti :$port)
    if [ ! -z "$pid" ]; then
        echo -e "${YELLOW}Stopping $name on port $port (PID: $pid)${NC}"
        kill -9 $pid 2>/dev/null
        sleep 1
        echo -e "${GREEN}✅ $name stopped${NC}"
        return 0
    else
        echo -e "${YELLOW}ℹ️  No $name process found on port $port${NC}"
        return 1
    fi
}

# Stop servers by PID files if they exist
stopped_any=false

if [ -f .backend.pid ]; then
    backend_pid=$(cat .backend.pid)
    if kill -0 $backend_pid 2>/dev/null; then
        kill $backend_pid
        echo -e "${GREEN}✅ Backend server stopped (PID: $backend_pid)${NC}"
        stopped_any=true
    fi
    rm -f .backend.pid
fi

if [ -f .frontend.pid ]; then
    frontend_pid=$(cat .frontend.pid)
    if kill -0 $frontend_pid 2>/dev/null; then
        kill $frontend_pid
        echo -e "${GREEN}✅ Frontend server stopped (PID: $frontend_pid)${NC}"
        stopped_any=true
    fi
    rm -f .frontend.pid
fi

# Also try to stop by port (backup method)
backend_stopped=$(kill_port 8000 "Backend API Server")
frontend_stopped=$(kill_port 5173 "Frontend Development Server")

if [ "$backend_stopped" == "0" ] || [ "$frontend_stopped" == "0" ] || [ "$stopped_any" == "true" ]; then
    echo ""
    echo -e "${GREEN}🎉 All servers have been stopped!${NC}"
else
    echo ""
    echo -e "${YELLOW}ℹ️  No running servers found${NC}"
fi

echo ""
echo -e "${YELLOW}💡 To start servers again, run: ./start-servers.sh${NC}"
