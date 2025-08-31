#!/bin/bash

# NewsAggregator Development Servers Startup Script
# This script starts both backend and frontend servers concurrently

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "🚀 Starting NewsAggregator Development Servers..."
echo "================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if port is in use
check_port() {
    local port=$1
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        return 0  # Port is in use
    else
        return 1  # Port is free
    fi
}

# Function to kill process on port
kill_port() {
    local port=$1
    local pid=$(lsof -ti :$port)
    if [ ! -z "$pid" ]; then
        echo -e "${YELLOW}Stopping existing process on port $port (PID: $pid)${NC}"
        kill -9 $pid 2>/dev/null
        sleep 1
    fi
}

# Check and kill existing processes on our ports
if check_port 8000; then
    kill_port 8000
fi

if check_port 5173; then
    kill_port 5173
fi

# Create storage directories if they don't exist
echo -e "${BLUE}📁 Creating storage directories...${NC}"
mkdir -p backend/storage/logs backend/storage/cache

# Start Backend Server (PHP API)
echo -e "${BLUE}🔧 Starting Backend API Server on http://localhost:8000${NC}"
if [ ! -d "backend/public" ]; then
    echo -e "${RED}❌ Backend public directory not found at backend/public${NC}"
    echo -e "${YELLOW}Current directory: $(pwd)${NC}"
    echo -e "${YELLOW}Contents: $(ls -la | head -5)${NC}"
    exit 1
fi
cd backend/public
php -S localhost:8000 > ../storage/logs/server.log 2>&1 &
BACKEND_PID=$!
cd "${SCRIPT_DIR}"

# Wait a moment for backend to start
sleep 2

# Check if backend started successfully
if check_port 8000; then
    echo -e "${GREEN}✅ Backend API Server started successfully (PID: $BACKEND_PID)${NC}"
else
    echo -e "${RED}❌ Failed to start Backend API Server${NC}"
    exit 1
fi

# Start Frontend Server (React TypeScript with Vite)
echo -e "${BLUE}🌐 Starting Frontend Development Server on http://localhost:5173${NC}"
if [ ! -d "frontend" ]; then
    echo -e "${RED}❌ Frontend directory not found at frontend${NC}"
    exit 1
fi

# Check if npm is available
if ! command -v npm &> /dev/null; then
    echo -e "${RED}❌ npm is not installed. Please install Node.js and npm.${NC}"
    kill $BACKEND_PID 2>/dev/null
    exit 1
fi

# Check if node_modules exists, if not run npm install
if [ ! -d "frontend/node_modules" ]; then
    echo -e "${YELLOW}📦 Installing frontend dependencies...${NC}"
    cd frontend
    npm install
    cd "${SCRIPT_DIR}"
fi

cd frontend
npm run dev > ../backend/storage/logs/frontend.log 2>&1 &
FRONTEND_PID=$!
cd "${SCRIPT_DIR}"

# Wait a moment for frontend to start
sleep 3

# Check if frontend started successfully
if check_port 5173; then
    echo -e "${GREEN}✅ Frontend Development Server started successfully (PID: $FRONTEND_PID)${NC}"
else
    echo -e "${RED}❌ Failed to start Frontend Development Server${NC}"
    echo -e "${YELLOW}Check the log: tail -f backend/storage/logs/frontend.log${NC}"
    kill $BACKEND_PID 2>/dev/null
    exit 1
fi

# Save PIDs for later cleanup
echo "$BACKEND_PID" > .backend.pid
echo "$FRONTEND_PID" > .frontend.pid

echo ""
echo -e "${GREEN}🎉 Both servers are now running!${NC}"
echo "================================================="
echo -e "${BLUE}📱 Frontend (React TypeScript): ${NC}http://localhost:5173"
echo -e "${BLUE}🔌 Backend (PHP API):           ${NC}http://localhost:8000"
echo -e "${BLUE}💊 Health Check:               ${NC}http://localhost:8000/health"
echo ""
echo -e "${YELLOW}📋 Quick Commands:${NC}"
echo "  • View backend logs: tail -f backend/storage/logs/server.log"
echo "  • View frontend logs: tail -f backend/storage/logs/frontend.log"
echo "  • Stop servers: ./stop-servers.sh"
echo "  • Check status: ./status-servers.sh"
echo ""
echo -e "${BLUE}💡 Tip: Keep this terminal open or press Ctrl+C to stop both servers${NC}"

# Function to cleanup on exit
cleanup() {
    echo ""
    echo -e "${YELLOW}🛑 Stopping servers...${NC}"
    
    if [ -f .backend.pid ]; then
        kill $(cat .backend.pid) 2>/dev/null
        rm -f .backend.pid
        echo -e "${GREEN}✅ Backend server stopped${NC}"
    fi
    
    if [ -f .frontend.pid ]; then
        kill $(cat .frontend.pid) 2>/dev/null
        rm -f .frontend.pid
        echo -e "${GREEN}✅ Frontend server stopped${NC}"
    fi
    
    echo -e "${GREEN}👋 Goodbye!${NC}"
    exit 0
}

# Trap SIGINT (Ctrl+C) and SIGTERM
trap cleanup SIGINT SIGTERM

# Keep script running and show real-time status
while true; do
    if ! check_port 8000; then
        echo -e "${RED}❌ Backend server stopped unexpectedly${NC}"
        cleanup
    fi
    
    if ! check_port 5173; then
        echo -e "${RED}❌ Frontend server stopped unexpectedly${NC}"
        cleanup
    fi
    
    sleep 5
done
