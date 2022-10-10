import React, { useEffect, useState } from "react";
import { StyleSheet, View, useColorScheme } from "react-native";
import { NativeRouter, Route, Routes, Link } from "react-router-native";
import { Ionicons } from '@expo/vector-icons';

// theme options
import { ThemeStyleColors } from "./config/Theme";
// context
import ThemeOptions from "./services/ThemeOptions";
// local storage
import { getData } from "./services/Store";
// language
import Language from "./config/Language";
// components
import useWindowSize from "./components/useWindowSize";

// Views
import Home from "./views/Home";
import Create from "./views/Create";
import Edit from "./views/Edit";
import Delete from "./views/Delete";
import Preview from "./views/Preview";
import Search from "./views/Search";
import Settings from "./views/Settings";
import NoMatch from "./views/NoMatch";

export default function App() {

  const [data,setData] = useState({})
  const [lang,setLang] = useState({})

  const [width, height] = useWindowSize();

  useEffect(() => {
    getData().then(r => {
      if(r){
        setData(r);
        setLang(Language[r.language ? r.language : "es"])
      }else{
        setData({});
        setLang(Language["es"])
      }
    })
  },[])

  return (
    <NativeRouter>
      <ThemeOptions.Provider value={{ data, lang }}>
        <View style={styles.mainApp}>
          <View style={styles.appHeader}>
            <View style={styles.appHeaderLeft}>
              <Link to="/" style={styles.appHeaderBtn}>
                <Ionicons name="home" size={21} color={ThemeStyleColors.warning} />
              </Link>
              <Link to="/create" style={styles.appHeaderBtn}>
              <Ionicons name="add-circle-outline" size={21} color={ThemeStyleColors.warning} />
              </Link>
            </View>
            <View style={styles.appHeaderRight}>
              <Link to="/search/author/anonymous" style={styles.appHeaderBtn}>
              <Ionicons name="search" size={21} color={ThemeStyleColors.warning} />
              </Link>
              <Link to="/settings" style={styles.appHeaderBtn}>
                <Ionicons name="settings" size={21} color={ThemeStyleColors.warning} />
              </Link>
            </View>
          </View>
          <View style={{ height: height - 50 }}>
            <Routes>
              <Route exact path="/" element={<Home />} />
              <Route path="/create" element={<Create />} />
              <Route path="/search/:filter/:val" element={<Search />} />
              <Route path="/settings" element={<Settings />} />
              <Route path="/preview/:id" element={<Preview />} />
              <Route path="/delete/:id" element={<Delete />} />
              <Route path="/edit/:id" element={<Edit />} />
              <Route path="*" element={<NoMatch />} />
            </Routes>
          </View>
        </View>
      </ThemeOptions.Provider>
    </NativeRouter>
  );
}

const styles = StyleSheet.create({
  mainApp: {
    display: "flex",
    flexDirection: "column",
    backgroundColor: ThemeStyleColors.secondary,
  },
  appHeader: {
    display: "flex",
    flexDirection: "row",
    justifyContent: "center",
    height: 50,
    backgroundColor: ThemeStyleColors.primary,
    borderBottomColor: ThemeStyleColors.info,
    borderBottomWidth: 4,
  },
  appHeaderLeft: {
    display: "flex",
    alignItems: "center",
    justifyContent: "flex-start",
    flexDirection: "row",
    flex: 1,
  },
  appHeaderRight: {
    display: "flex",
    alignItems: "center",
    justifyContent: "flex-end",
    flexDirection: "row",
    flex: 1,
  },
  appHeaderBtn: {
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    width: 35,
    height: 35,
    padding: 8,
    margin: 5,
    borderRadius: 4,
    backgroundColor: ThemeStyleColors.secondary,
  },
});
