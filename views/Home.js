import React, { useContext, useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-native";
import {
  View,
  StyleSheet,
  Button,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
} from "react-native";

// context
import ThemeOptions from "../services/ThemeOptions";
// theme options
import { ThemeStyleColors } from "../config/Theme";
// services
import {getDb} from "../services/Store";
// components
import Card from "../components/Cards";
import useWindowSize from "../components/useWindowSize"

export default function Home() {
  const navigate = useNavigate();
  const config = useContext(ThemeOptions);
  const scrollRef = useRef(null);
  const [data, setData] = useState({});
  const [limit, setLimit] = useState(5);
  const [loaded, setLoaded] = useState({});

  const [width, height] = useWindowSize();

  const LoadInitialData = async () => {
    setLoaded(false);
    try {
      const response = await getDb("snippets", `all=1&limit=${limit}`);
      if(response){
        setData(response);
        setLimit(limit + 3);
      }
    } catch (e) {
      console.log(e);
    } finally {
      setLoaded(true);
    }
  };

  const handleLoadMore = () =>
    LoadInitialData().then(() =>
      scrollRef.current.scrollToEnd({ animated: false })
    );

  useEffect(LoadInitialData, []);

  if (!loaded) {
    return (
      <View style={[styles.container, styles.horizontal]}>
        <ActivityIndicator size="large" color={ThemeStyleColors.warning} />
      </View>
    );
  }
  if (!config.data.author) {
    navigate("/settings");
  }
  return (
    loaded && (
      <SafeAreaView style={styles.container}>
        <ScrollView ref={scrollRef}>
          {width > 600 ? (
            <View style={styles.gridContainer}>
              {Array.from(data).map((item, index) => (
                <View style={{width: (width / 3)}} key={`id_${index.toString()}`}>
                  <Card data={item} />
                </View>
              ))}
            </View>
          ) : (
            Array.from(data).map((item, index) => (
              <Card data={item} key={`id_${index.toString()}`} />
            ))
          )}
        </ScrollView>
        <Button
          color={ThemeStyleColors.primary}
          title={config.lang.loadMore || ''}
          onPress={handleLoadMore}
        />
      </SafeAreaView>
    )
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: "center",
    padding: 10,
  },
  gridContainer: {
    flex: 1,
    flexDirection: "row",
    flexWrap: "wrap",
    alignItems: "flex-start",
  },
  horizontal: {
    flexDirection: "row",
    justifyContent: "space-around",
    padding: 10,
  },
});
