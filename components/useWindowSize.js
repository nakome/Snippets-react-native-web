import { useLayoutEffect, useState } from "react";
import { Platform } from "react-native";

export default function useWindowSize() {
  const [size, setSize] = useState([0, 0]);
  useLayoutEffect(() => {
    function updateSize() {
      setSize([window.innerWidth, window.innerHeight]);
    }
    if (Platform.OS === "web") {
      window.addEventListener("resize", updateSize);
      updateSize();
      return () => window.removeEventListener("resize", updateSize);
    }
  }, []);
  return size;
}
